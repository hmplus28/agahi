"""Queue idempotent SMS notifications for advertisement expiration events."""
from datetime import timedelta

from django.core.management.base import BaseCommand
from django.utils import timezone

from ads.models import Ad, AdStatus
from notifications.models import SMSLog


class Command(BaseCommand):
    help = 'Queue SMS logs for advertisements that are expiring or newly expired.'

    def add_arguments(self, parser):
        parser.add_argument('--type', choices=['expiring_soon', 'expired', 'all'], default='expiring_soon')
        parser.add_argument('--days', type=int, default=3, help='Days before expiration for warning messages.')

    def handle(self, *args, **options):
        created = 0
        if options['type'] in {'expiring_soon', 'all'}:
            created += self._queue_expiring(max(options['days'], 1))
        if options['type'] in {'expired', 'all'}:
            created += self._queue_expired()
        self.stdout.write(self.style.SUCCESS(f'{created} اعلان برای ارسال در صف ثبت شد.'))

    @staticmethod
    def _logged_recently(ad, marker, now):
        return SMSLog.objects.filter(
            ad=ad,
            type='ad_expiry',
            message__startswith=marker,
            created_at__gte=now - timedelta(hours=24),
        ).exists()

    def _queue_expiring(self, days):
        now = timezone.now()
        end = now + timedelta(days=days)
        ads = Ad.objects.filter(
            status=AdStatus.ACTIVE,
            expires_at__gt=now,
            expires_at__lte=end,
            deleted_at__isnull=True,
        ).select_related('user')
        count = 0
        marker = 'هشدار تمدید:'
        for ad in ads:
            if self._logged_recently(ad, marker, now):
                continue
            SMSLog.objects.create(
                user=ad.user,
                ad=ad,
                mobile=ad.user.mobile,
                type='ad_expiry',
                message=f'{marker} آگهی «{ad.title}» تا {days} روز دیگر منقضی می‌شود.',
                status='pending',
            )
            count += 1
        return count

    def _queue_expired(self):
        now = timezone.now()
        ads = Ad.objects.filter(
            status=AdStatus.EXPIRED,
            updated_at__gte=now - timedelta(days=1),
            deleted_at__isnull=True,
        ).select_related('user')
        count = 0
        marker = 'انقضای آگهی:'
        for ad in ads:
            if self._logged_recently(ad, marker, now):
                continue
            SMSLog.objects.create(
                user=ad.user,
                ad=ad,
                mobile=ad.user.mobile,
                type='ad_expiry',
                message=f'{marker} آگهی «{ad.title}» منقضی شد.',
                status='pending',
            )
            count += 1
        return count
