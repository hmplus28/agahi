from django.test import TestCase, override_settings
from django.utils import timezone

from notifications.models import SMSLog
from notifications.services.dispatch import dispatch_pending_sms
from notifications.services.sms_gateway import KavenegarSMSAdapter, SMSGatewayAdapter, SMSGatewayFactory, SMSRequest, SMSResponse


class SuccessfulAdapter(SMSGatewayAdapter):
    name = 'test-success'

    @property
    def is_configured(self):
        return True

    def send(self, request: SMSRequest):
        return SMSResponse(success=True, provider_id='provider-123', raw_response='accepted')


class FailingAdapter(SMSGatewayAdapter):
    name = 'test-failure'

    @property
    def is_configured(self):
        return True

    def send(self, request: SMSRequest):
        return SMSResponse(success=False, error_code='temporary', error_message='provider unavailable')


class NotificationDispatchTests(TestCase):
    @override_settings(SMS_ENABLED=True, SMS_MAX_ATTEMPTS=2)
    def test_dispatch_marks_successful_log_sent(self):
        log = SMSLog.objects.create(mobile='09120000000', type='ad_expiry', message='یادآوری تمدید')
        result = dispatch_pending_sms(adapter=SuccessfulAdapter())
        log.refresh_from_db()
        self.assertEqual(result['sent'], 1)
        self.assertEqual(log.status, 'sent')
        self.assertEqual(log.provider_id, 'provider-123')
        self.assertEqual(log.attempts, 1)
        self.assertIsNotNone(log.sent_at)
        self.assertIsNone(log.next_attempt_at)

    @override_settings(SMS_ENABLED=True, SMS_MAX_ATTEMPTS=2)
    def test_dispatch_retries_then_marks_terminal_failure(self):
        log = SMSLog.objects.create(mobile='09120000000', type='ad_expiry', message='یادآوری تمدید')
        first = dispatch_pending_sms(adapter=FailingAdapter())
        log.refresh_from_db()
        self.assertEqual(first['retried'], 1)
        self.assertEqual(log.status, 'pending')
        self.assertEqual(log.attempts, 1)
        self.assertGreater(log.next_attempt_at, timezone.now())

        log.next_attempt_at = timezone.now()
        log.save(update_fields=['next_attempt_at'])
        second = dispatch_pending_sms(adapter=FailingAdapter())
        log.refresh_from_db()
        self.assertEqual(second['failed'], 1)
        self.assertEqual(log.status, 'failed')
        self.assertEqual(log.attempts, 2)
        self.assertIsNone(log.next_attempt_at)

    @override_settings(SMS_ENABLED=False)
    def test_disabled_provider_keeps_pending_queue_untouched(self):
        log = SMSLog.objects.create(mobile='09120000000', type='ad_expiry', message='یادآوری تمدید')
        result = dispatch_pending_sms()
        log.refresh_from_db()
        self.assertEqual(result['skipped_disabled'], 1)
        self.assertEqual(log.status, 'pending')
        self.assertEqual(log.attempts, 0)

    @override_settings(OFFLINE_MODE=True, SMS_ENABLED=True, SMS_PROVIDER='kavenegar', KAVENEGAR_API_KEY='configured-key')
    def test_offline_mode_disables_external_sms_adapter(self):
        adapter = SMSGatewayFactory.get_default_adapter()
        self.assertFalse(adapter.is_configured)
        self.assertEqual(adapter.name, 'disabled')
        direct_result = KavenegarSMSAdapter('configured-key').send(SMSRequest(mobile='09120000000', message='test'))
        self.assertEqual(direct_result.error_code, 'offline_mode')
