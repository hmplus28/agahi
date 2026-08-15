"""Transactional dispatch of queued SMS logs with bounded retry scheduling."""
from datetime import timedelta

from django.conf import settings
from django.db import transaction
from django.db.models import Q
from django.utils import timezone

from notifications.models import SMSLog

from .sms_gateway import SMSGatewayFactory, SMSRequest


def _next_retry_at(now, attempts):
    delay_seconds = min(60 * (2 ** max(attempts - 1, 0)), 3600)
    return now + timedelta(seconds=delay_seconds)


def dispatch_pending_sms(*, batch_size=100, adapter=None):
    """Attempt a bounded batch of due messages and return operational counters.

    A row lock prevents concurrent workers from dispatching the same queued log.
    Failed provider calls are retried with exponential backoff until the configured
    maximum; a disabled provider intentionally leaves the queue untouched.
    """
    adapter = adapter or SMSGatewayFactory.get_default_adapter()
    counters = {'sent': 0, 'retried': 0, 'failed': 0, 'skipped_disabled': 0}
    if not adapter.is_configured:
        counters['skipped_disabled'] = SMSLog.objects.filter(status='pending').count()
        return counters

    now = timezone.now()
    due_ids = list(
        SMSLog.objects.filter(status='pending')
        .filter(Q(next_attempt_at__isnull=True) | Q(next_attempt_at__lte=now))
        .order_by('created_at')
        .values_list('pk', flat=True)[:batch_size]
    )
    max_attempts = max(1, int(getattr(settings, 'SMS_MAX_ATTEMPTS', 3)))
    for log_id in due_ids:
        with transaction.atomic():
            log = SMSLog.objects.select_for_update().filter(pk=log_id, status='pending').first()
            if not log or (log.next_attempt_at and log.next_attempt_at > timezone.now()):
                continue
            result = adapter.send(SMSRequest(mobile=log.mobile, message=log.message))
            log.attempts += 1
            log.last_attempt_at = timezone.now()
            if result.success:
                log.status = 'sent'
                log.provider_id = result.provider_id[:100]
                log.response = result.raw_response[:2000]
                log.sent_at = log.last_attempt_at
                log.next_attempt_at = None
                counters['sent'] += 1
            else:
                log.response = f'{result.error_code}: {result.error_message}'.strip(': ')[:2000]
                if log.attempts >= max_attempts:
                    log.status = 'failed'
                    log.next_attempt_at = None
                    counters['failed'] += 1
                else:
                    log.next_attempt_at = _next_retry_at(log.last_attempt_at, log.attempts)
                    counters['retried'] += 1
            log.save(update_fields=[
                'status', 'provider_id', 'response', 'attempts', 'last_attempt_at',
                'next_attempt_at', 'sent_at',
            ])
    return counters
