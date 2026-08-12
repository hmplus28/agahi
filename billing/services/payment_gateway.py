"""
Payment gateway service with adapter pattern.
Supports multiple payment providers (ZarinPal, NextPay, IDPay, etc.).
"""
from abc import ABC, abstractmethod
from typing import Optional, Dict, Any
from dataclasses import dataclass
from django.conf import settings


@dataclass
class PaymentRequest:
    """Payment request data."""
    amount: int  # In Tomans
    callback_url: str
    description: str
    mobile: Optional[str] = None
    email: Optional[str] = None


@dataclass
class PaymentResponse:
    """Payment response data."""
    success: bool
    authority: Optional[str] = None
    redirect_url: Optional[str] = None
    error_code: Optional[str] = None
    error_message: Optional[str] = None


@dataclass
class VerificationResult:
    """Payment verification result."""
    success: bool
    ref_id: Optional[str] = None
    card_number: Optional[str] = None
    error_code: Optional[str] = None
    error_message: Optional[str] = None


class PaymentGatewayAdapter(ABC):
    """Abstract base class for payment gateway adapters."""
    
    @abstractmethod
    def request_payment(self, request: PaymentRequest) -> PaymentResponse:
        """Request payment from gateway."""
        pass
    
    @abstractmethod
    def verify_payment(self, authority: str, amount: int) -> VerificationResult:
        """Verify payment with gateway."""
        pass


class ZarinPalAdapter(PaymentGatewayAdapter):
    """ZarinPal payment gateway adapter."""
    
    GATEWAY_NAME = 'zarinpal'
    SANDBOX_URL = 'https://sandbox.zarinpal.com/pg/v4/payment'
    PRODUCTION_URL = 'https://api.zarinpal.com/pg/v4/payment'
    
    def __init__(self, merchant_id: str, sandbox: bool = True):
        self.merchant_id = merchant_id
        self.base_url = self.SANDBOX_URL if sandbox else self.PRODUCTION_URL
        self.sandbox = sandbox
    
    def request_payment(self, request: PaymentRequest) -> PaymentResponse:
        """Request payment from ZarinPal."""
        try:
            import requests
            
            payload = {
                "merchant_id": self.merchant_id,
                "amount": request.amount,
                "callback_url": request.callback_url,
                "description": request.description,
            }
            
            if request.mobile:
                payload["mobile"] = request.mobile
            if request.email:
                payload["email"] = request.email
            
            response = requests.post(
                f"{self.base_url}/request.json",
                json=payload,
                timeout=30
            )
            response.raise_for_status()
            data = response.json()
            
            if data.get('errors') is None or len(data.get('errors', [])) == 0:
                authority = data['data']['authority']
                redirect_url = f"https://{'sandbox' if self.sandbox else 'www'}.zarinpal.com/pg/StartPay/{authority}"
                
                return PaymentResponse(
                    success=True,
                    authority=authority,
                    redirect_url=redirect_url
                )
            else:
                error = data['errors'][0]
                return PaymentResponse(
                    success=False,
                    error_code=error.get('code'),
                    error_message=error.get('message')
                )
                
        except Exception as e:
            return PaymentResponse(
                success=False,
                error_message=str(e)
            )
    
    def verify_payment(self, authority: str, amount: int) -> VerificationResult:
        """Verify payment with ZarinPal."""
        try:
            import requests
            
            payload = {
                "merchant_id": self.merchant_id,
                "amount": amount,
                "authority": authority,
            }
            
            response = requests.post(
                f"{self.base_url}/verification.json",
                json=payload,
                timeout=30
            )
            response.raise_for_status()
            data = response.json()
            
            if data.get('errors') is None or len(data.get('errors', [])) == 0:
                return VerificationResult(
                    success=True,
                    ref_id=data['data'].get('ref_id'),
                    card_number=data['data'].get('card_pan')
                )
            else:
                error = data['errors'][0]
                return VerificationResult(
                    success=False,
                    error_code=error.get('code'),
                    error_message=error.get('message')
                )
                
        except Exception as e:
            return VerificationResult(
                success=False,
                error_message=str(e)
            )


class NextPayAdapter(PaymentGatewayAdapter):
    """NextPay payment gateway adapter."""
    
    GATEWAY_NAME = 'nextpay'
    SANDBOX_URL = 'https://sandbox.nextpay.org'
    PRODUCTION_URL = 'https://api.nextpay.org'
    
    def __init__(self, api_key: str, sandbox: bool = True):
        self.api_key = api_key
        self.base_url = self.SANDBOX_URL if sandbox else self.PRODUCTION_URL
    
    def request_payment(self, request: PaymentRequest) -> PaymentResponse:
        """Request payment from NextPay."""
        try:
            import requests
            
            payload = {
                "api_key": self.api_key,
                "amount": request.amount,
                "callback": request.callback_url,
                "description": request.description,
            }
            
            if request.mobile:
                payload["mobile"] = request.mobile
            if request.email:
                payload["email"] = request.email
            
            response = requests.post(
                f"{self.base_url}/gateway/pip",
                data=payload,
                timeout=30
            )
            response.raise_for_status()
            data = response.json()
            
            if data.get('code') == -1:
                authority = data.get('trans_id')
                redirect_url = f"{self.base_url}/gateway/trans_{authority}"
                
                return PaymentResponse(
                    success=True,
                    authority=authority,
                    redirect_url=redirect_url
                )
            else:
                return PaymentResponse(
                    success=False,
                    error_code=str(data.get('code')),
                    error_message=data.get('message')
                )
                
        except Exception as e:
            return PaymentResponse(
                success=False,
                error_message=str(e)
            )
    
    def verify_payment(self, authority: str, amount: int) -> VerificationResult:
        """Verify payment with NextPay."""
        try:
            import requests
            
            payload = {
                "api_key": self.api_key,
                "amount": amount,
                "trans_id": authority,
            }
            
            response = requests.post(
                f"{self.base_url}/gateway/verify",
                data=payload,
                timeout=30
            )
            response.raise_for_status()
            data = response.json()
            
            if data.get('code') == 0:
                return VerificationResult(
                    success=True,
                    ref_id=data.get('ShaparakRefId'),
                    card_number=data.get('card_no')
                )
            else:
                return VerificationResult(
                    success=False,
                    error_code=str(data.get('code')),
                    error_message=data.get('message')
                )
                
        except Exception as e:
            return VerificationResult(
                success=False,
                error_message=str(e)
            )


class PaymentGatewayFactory:
    """Factory for creating payment gateway adapters."""
    
    _adapters: Dict[str, PaymentGatewayAdapter] = {}
    
    @classmethod
    def register_adapter(cls, name: str, adapter: PaymentGatewayAdapter):
        """Register a payment gateway adapter."""
        cls._adapters[name] = adapter
    
    @classmethod
    def get_adapter(cls, name: str) -> Optional[PaymentGatewayAdapter]:
        """Get a registered payment gateway adapter."""
        return cls._adapters.get(name)
    
    @classmethod
    def get_default_adapter(cls) -> PaymentGatewayAdapter:
        """Get the default payment gateway adapter."""
        default_gateway = getattr(settings, 'DEFAULT_PAYMENT_GATEWAY', 'zarinpal')
        adapter = cls.get_adapter(default_gateway)
        
        if not adapter:
            # Create default ZarinPal adapter
            merchant_id = getattr(settings, 'ZARINPAL_MERCHANT_ID', '')
            sandbox = getattr(settings, 'ZARINPAL_SANDBOX', True)
            adapter = ZarinPalAdapter(merchant_id, sandbox)
            cls.register_adapter('zarinpal', adapter)
        
        return adapter


# Initialize default adapters
def initialize_payment_gateways():
    """Initialize payment gateway adapters from settings."""
    # ZarinPal
    zarinpal_merchant = getattr(settings, 'ZARINPAL_MERCHANT_ID', '')
    zarinpal_sandbox = getattr(settings, 'ZARINPAL_SANDBOX', True)
    if zarinpal_merchant:
        PaymentGatewayFactory.register_adapter(
            'zarinpal',
            ZarinPalAdapter(zarinpal_merchant, zarinpal_sandbox)
        )
    
    # NextPay
    nextpay_api_key = getattr(settings, 'NEXTPAY_API_KEY', '')
    nextpay_sandbox = getattr(settings, 'NEXTPAY_SANDBOX', True)
    if nextpay_api_key:
        PaymentGatewayFactory.register_adapter(
            'nextpay',
            NextPayAdapter(nextpay_api_key, nextpay_sandbox)
        )


# Convenience functions
def request_payment(amount: int, callback_url: str, description: str, **kwargs) -> PaymentResponse:
    """Request payment using default gateway."""
    gateway = PaymentGatewayFactory.get_default_adapter()
    request_obj = PaymentRequest(
        amount=amount,
        callback_url=callback_url,
        description=description,
        **kwargs
    )
    return gateway.request_payment(request_obj)


def verify_payment(authority: str, amount: int) -> VerificationResult:
    """Verify payment using default gateway."""
    gateway = PaymentGatewayFactory.get_default_adapter()
    return gateway.verify_payment(authority, amount)
