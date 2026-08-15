"""Provider-neutral payment gateway adapters; all credentials are supplied through settings."""
from abc import ABC, abstractmethod
from dataclasses import dataclass
from typing import Optional

from django.conf import settings

from core.services.http_client import HTTPClientError, post_form, post_json


@dataclass(frozen=True)
class PaymentRequest:
    amount: int
    callback_url: str
    description: str
    mobile: Optional[str] = None
    email: Optional[str] = None


@dataclass(frozen=True)
class PaymentResponse:
    success: bool
    authority: str = ''
    redirect_url: str = ''
    error_code: str = ''
    error_message: str = ''


@dataclass(frozen=True)
class VerificationResult:
    success: bool
    ref_id: str = ''
    card_number: str = ''
    error_code: str = ''
    error_message: str = ''


class PaymentGatewayConfigurationError(RuntimeError):
    """Raised only when a non-free payment is attempted without configured credentials."""


class PaymentGatewayAdapter(ABC):
    name = 'base'

    @property
    @abstractmethod
    def is_configured(self) -> bool:
        """Whether this adapter has all credentials required to start a payment."""

    @abstractmethod
    def request_payment(self, request: PaymentRequest) -> PaymentResponse:
        """Create a provider payment session."""

    @abstractmethod
    def verify_payment(self, authority: str, amount: int) -> VerificationResult:
        """Verify the provider callback with the original immutable amount."""


class ZarinPalAdapter(PaymentGatewayAdapter):
    name = 'zarinpal'
    SANDBOX_URL = 'https://sandbox.zarinpal.com/pg/v4/payment'
    PRODUCTION_URL = 'https://api.zarinpal.com/pg/v4/payment'

    def __init__(self, merchant_id: str, sandbox: bool = True, timeout: int = 20):
        self.merchant_id = merchant_id
        self.sandbox = sandbox
        self.timeout = timeout
        self.base_url = self.SANDBOX_URL if sandbox else self.PRODUCTION_URL

    @property
    def is_configured(self) -> bool:
        return bool(self.merchant_id)

    def request_payment(self, request: PaymentRequest) -> PaymentResponse:
        if getattr(settings, 'OFFLINE_MODE', False):
            return PaymentResponse(success=False, error_code='offline_mode', error_message='پرداخت اینترنتی در حالت آفلاین غیرفعال است.')
        if not self.is_configured:
            return PaymentResponse(success=False, error_code='not_configured', error_message='درگاه پرداخت پیکربندی نشده است.')
        payload = {
            'merchant_id': self.merchant_id,
            'amount': request.amount,
            'callback_url': request.callback_url,
            'description': request.description[:255],
        }
        if request.mobile:
            payload['mobile'] = request.mobile
        if request.email:
            payload['email'] = request.email
        try:
            response = post_json(f'{self.base_url}/request.json', payload, self.timeout)
            body = response.data
            data = body.get('data') or {}
            if data.get('code') == 100 and data.get('authority'):
                authority = str(data['authority'])
                host = 'sandbox' if self.sandbox else 'www'
                return PaymentResponse(success=True, authority=authority, redirect_url=f'https://{host}.zarinpal.com/pg/StartPay/{authority}')
            error = body.get('errors') or data
            return PaymentResponse(
                success=False,
                error_code=str(error.get('code', 'provider_error')),
                error_message=str(error.get('message', 'درخواست پرداخت توسط درگاه پذیرفته نشد.'))[:500],
            )
        except HTTPClientError as exc:
            return PaymentResponse(success=False, error_code='network_error', error_message=str(exc)[:500])
        except (TypeError, ValueError) as exc:
            return PaymentResponse(success=False, error_code='response_error', error_message=str(exc)[:500])

    def verify_payment(self, authority: str, amount: int) -> VerificationResult:
        if getattr(settings, 'OFFLINE_MODE', False):
            return VerificationResult(success=False, error_code='offline_mode', error_message='تأیید پرداخت اینترنتی در حالت آفلاین غیرفعال است.')
        if not self.is_configured:
            return VerificationResult(success=False, error_code='not_configured', error_message='درگاه پرداخت پیکربندی نشده است.')
        try:
            response = post_json(
                f'{self.base_url}/verify.json',
                {'merchant_id': self.merchant_id, 'amount': amount, 'authority': authority},
                self.timeout,
            )
            body = response.data
            data = body.get('data') or {}
            if data.get('code') in {100, 101}:
                return VerificationResult(
                    success=True,
                    ref_id=str(data.get('ref_id', '')),
                    card_number=str(data.get('card_pan', '')),
                )
            error = body.get('errors') or data
            return VerificationResult(
                success=False,
                error_code=str(error.get('code', 'provider_error')),
                error_message=str(error.get('message', 'تأیید پرداخت ناموفق بود.'))[:500],
            )
        except HTTPClientError as exc:
            return VerificationResult(success=False, error_code='network_error', error_message=str(exc)[:500])
        except (TypeError, ValueError) as exc:
            return VerificationResult(success=False, error_code='response_error', error_message=str(exc)[:500])


class NextPayAdapter(PaymentGatewayAdapter):
    name = 'nextpay'
    SANDBOX_URL = 'https://sandbox.nextpay.org'
    PRODUCTION_URL = 'https://api.nextpay.org'

    def __init__(self, api_key: str, sandbox: bool = True, timeout: int = 20):
        self.api_key = api_key
        self.base_url = self.SANDBOX_URL if sandbox else self.PRODUCTION_URL
        self.timeout = timeout

    @property
    def is_configured(self) -> bool:
        return bool(self.api_key)

    def request_payment(self, request: PaymentRequest) -> PaymentResponse:
        if getattr(settings, 'OFFLINE_MODE', False):
            return PaymentResponse(success=False, error_code='offline_mode', error_message='پرداخت اینترنتی در حالت آفلاین غیرفعال است.')
        if not self.is_configured:
            return PaymentResponse(success=False, error_code='not_configured', error_message='درگاه پرداخت پیکربندی نشده است.')
        payload = {'api_key': self.api_key, 'amount': request.amount, 'callback': request.callback_url, 'description': request.description[:255]}
        if request.mobile:
            payload['mobile'] = request.mobile
        if request.email:
            payload['email'] = request.email
        try:
            response = post_form(f'{self.base_url}/gateway/pip', payload, self.timeout)
            data = response.data
            if data.get('code') == -1 and data.get('trans_id'):
                authority = str(data['trans_id'])
                return PaymentResponse(success=True, authority=authority, redirect_url=f'{self.base_url}/gateway/trans_{authority}')
            return PaymentResponse(success=False, error_code=str(data.get('code', 'provider_error')), error_message=str(data.get('message', 'درخواست پرداخت ناموفق بود.'))[:500])
        except HTTPClientError as exc:
            return PaymentResponse(success=False, error_code='network_error', error_message=str(exc)[:500])
        except (TypeError, ValueError) as exc:
            return PaymentResponse(success=False, error_code='response_error', error_message=str(exc)[:500])

    def verify_payment(self, authority: str, amount: int) -> VerificationResult:
        if getattr(settings, 'OFFLINE_MODE', False):
            return VerificationResult(success=False, error_code='offline_mode', error_message='تأیید پرداخت اینترنتی در حالت آفلاین غیرفعال است.')
        if not self.is_configured:
            return VerificationResult(success=False, error_code='not_configured', error_message='درگاه پرداخت پیکربندی نشده است.')
        try:
            response = post_form(
                f'{self.base_url}/gateway/verify',
                {'api_key': self.api_key, 'amount': amount, 'trans_id': authority},
                self.timeout,
            )
            data = response.data
            if data.get('code') == 0:
                return VerificationResult(success=True, ref_id=str(data.get('ShaparakRefId', '')), card_number=str(data.get('card_no', '')))
            return VerificationResult(success=False, error_code=str(data.get('code', 'provider_error')), error_message=str(data.get('message', 'تأیید پرداخت ناموفق بود.'))[:500])
        except HTTPClientError as exc:
            return VerificationResult(success=False, error_code='network_error', error_message=str(exc)[:500])
        except (TypeError, ValueError) as exc:
            return VerificationResult(success=False, error_code='response_error', error_message=str(exc)[:500])


class PaymentGatewayFactory:
    @staticmethod
    def get_adapter(provider: str | None = None) -> PaymentGatewayAdapter:
        if getattr(settings, 'OFFLINE_MODE', False):
            raise PaymentGatewayConfigurationError('سامانه در حالت آفلاین است؛ آغاز یا تأیید پرداخت اینترنتی موقتاً غیرفعال است.')
        provider = (provider or getattr(settings, 'DEFAULT_PAYMENT_GATEWAY', 'zarinpal')).lower().strip()
        timeout = int(getattr(settings, 'PAYMENT_HTTP_TIMEOUT', 20))
        if provider == 'zarinpal':
            adapter = ZarinPalAdapter(getattr(settings, 'ZARINPAL_MERCHANT_ID', ''), getattr(settings, 'ZARINPAL_SANDBOX', True), timeout)
        elif provider == 'nextpay':
            adapter = NextPayAdapter(getattr(settings, 'NEXTPAY_API_KEY', ''), getattr(settings, 'NEXTPAY_SANDBOX', True), timeout)
        else:
            raise PaymentGatewayConfigurationError('نام درگاه پرداخت انتخاب‌شده معتبر نیست.')
        if not adapter.is_configured:
            raise PaymentGatewayConfigurationError('درگاه پرداخت هنوز پیکربندی نشده است.')
        return adapter

    @classmethod
    def get_default_adapter(cls) -> PaymentGatewayAdapter:
        return cls.get_adapter()


def request_payment(amount: int, callback_url: str, description: str, **kwargs) -> PaymentResponse:
    return PaymentGatewayFactory.get_default_adapter().request_payment(
        PaymentRequest(amount=int(amount), callback_url=callback_url, description=description, **kwargs)
    )


def verify_payment(authority: str, amount: int, provider: str | None = None) -> VerificationResult:
    return PaymentGatewayFactory.get_adapter(provider).verify_payment(authority, int(amount))
