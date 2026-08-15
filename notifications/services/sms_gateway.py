"""Provider-neutral SMS gateway adapters configured exclusively through environment-backed settings."""
from abc import ABC, abstractmethod
from dataclasses import dataclass
from typing import Optional

import requests
from django.conf import settings


@dataclass(frozen=True)
class SMSRequest:
    mobile: str
    message: str


@dataclass(frozen=True)
class SMSResponse:
    success: bool
    provider_id: str = ''
    error_code: str = ''
    error_message: str = ''
    raw_response: str = ''


class SMSGatewayAdapter(ABC):
    name = 'base'

    @property
    def is_configured(self) -> bool:
        return True

    @abstractmethod
    def send(self, request: SMSRequest) -> SMSResponse:
        """Deliver exactly one message and return a sanitized provider result."""


class DisabledSMSAdapter(SMSGatewayAdapter):
    name = 'disabled'

    @property
    def is_configured(self) -> bool:
        return False

    def send(self, request: SMSRequest) -> SMSResponse:
        return SMSResponse(success=False, error_code='not_configured', error_message='ارسال پیامک پیکربندی نشده است.')


class ConsoleSMSAdapter(SMSGatewayAdapter):
    """Non-production adapter for controlled development and automated tests."""
    name = 'console'

    def send(self, request: SMSRequest) -> SMSResponse:
        return SMSResponse(success=True, provider_id=f'console-{request.mobile[-4:]}', raw_response='console delivery')


class KavenegarSMSAdapter(SMSGatewayAdapter):
    """Kavenegar line-based adapter; credentials are never embedded in code or logs."""
    name = 'kavenegar'

    def __init__(self, api_key: str, sender: str = '', timeout: int = 15):
        self.api_key = api_key
        self.sender = sender
        self.timeout = timeout

    @property
    def is_configured(self) -> bool:
        return bool(self.api_key)

    def send(self, request: SMSRequest) -> SMSResponse:
        if not self.is_configured:
            return DisabledSMSAdapter().send(request)
        try:
            payload = {'receptor': request.mobile, 'message': request.message}
            if self.sender:
                payload['sender'] = self.sender
            response = requests.post(
                f'https://api.kavenegar.com/v1/{self.api_key}/sms/send.json',
                data=payload,
                timeout=self.timeout,
            )
            raw = response.text[:2000]
            response.raise_for_status()
            data = response.json()
            entries = data.get('entries') or []
            if entries:
                provider_id = str(entries[0].get('messageid', ''))
                return SMSResponse(success=True, provider_id=provider_id, raw_response=raw)
            return SMSResponse(success=False, error_code='unexpected_response', error_message='پاسخ معتبر از سرویس پیامک دریافت نشد.', raw_response=raw)
        except requests.RequestException as exc:
            return SMSResponse(success=False, error_code='network_error', error_message=str(exc)[:500])
        except (TypeError, ValueError) as exc:
            return SMSResponse(success=False, error_code='response_error', error_message=str(exc)[:500])


class SMSGatewayFactory:
    """Create one configured adapter by provider name, with disabled as the safe default."""

    @classmethod
    def get_default_adapter(cls) -> SMSGatewayAdapter:
        if not getattr(settings, 'SMS_ENABLED', False):
            return DisabledSMSAdapter()
        provider = getattr(settings, 'SMS_PROVIDER', 'disabled').lower().strip()
        if provider == 'console':
            return ConsoleSMSAdapter()
        if provider == 'kavenegar':
            return KavenegarSMSAdapter(
                api_key=getattr(settings, 'KAVENEGAR_API_KEY', ''),
                sender=getattr(settings, 'SMS_SENDER_ID', ''),
                timeout=getattr(settings, 'SMS_HTTP_TIMEOUT', 15),
            )
        return DisabledSMSAdapter()
