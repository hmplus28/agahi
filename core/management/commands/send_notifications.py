"""
Management command to send notifications.
Sends SMS and email notifications for various events.
"""
from django.core.management.base import BaseCommand
from django.utils import timezone
from datetime import timedelta
from ads.models import Ad
from notifications.models import SMSLog


class Command(BaseCommand):
    help = 'Send scheduled notifications (expiring ads, etc.)'

    def add_arguments(self, parser):
        parser.add_argument(
            '--type',
            type=str,
            choices=['expiring_soon', 'expired', 'all'],
            default='expiring_soon',
            help='Type of notifications to send'
        )
        parser.add_argument(
            '--days',
            type=int,
            default=3,
            help='Days until expiration for warning (default: 3)'
        )

    def handle(self, *args, **options):
        notification_type = options['type']
        days = options['days']
        
        if notification_type in ['expiring_soon', 'all']:
            self._send_expiring_soon_notifications(days)
        
        if notification_type in ['expired', 'all']:
            self._send_expired_notifications()
        
        self.stdout.write(self.style.SUCCESS('Notification process completed.'))

    def _send_expiring_soon_notifications(self, days):
        """Send notifications for ads expiring soon."""
        now = timezone.now()
        expires_soon = now + timedelta(days=days)
        
        expiring_ads = Ad.objects.filter(
            status='active',
            expires_at__lte=expires_soon,
            expires_at__gt=now
        ).select_related('owner')
        
        count = 0
        for ad in expiring_ads:
            # Check if already notified
            if not SMSLog.objects.filter(
                recipient=ad.owner.mobile,
                message__icontains='منقضی',
                created_at__gte=now - timedelta(hours=24)
            ).exists():
                # Send SMS (placeholder - integrate with SMS provider)
                message = f'آگهی "{ad.title}" شما تا {days} روز دیگر منقضی می‌شود.'
                SMSLog.objects.create(
                    recipient=ad.owner.mobile,
                    message=message,
                    status='pending'
                )
                count += 1
        
        self.stdout.write(f'Sent {count} expiring soon notifications.')

    def _send_expired_notifications(self):
        """Send notifications for expired ads."""
        now = timezone.now()
        
        expired_ads = Ad.objects.filter(
            status='expired',
            updated_at__gte=now - timedelta(days=1)
        ).select_related('owner')
        
        count = 0
        for ad in expired_ads:
            # Check if already notified
            if not SMSLog.objects.filter(
                recipient=ad.owner.mobile,
                message__icontains='منقضی شد',
                created_at__gte=now - timedelta(hours=24)
            ).exists():
                # Send SMS (placeholder - integrate with SMS provider)
                message = f'آگهی "{ad.title}" شما منقضی شد.'
                SMSLog.objects.create(
                    recipient=ad.owner.mobile,
                    message=message,
                    status='pending'
                )
                count += 1
        
        self.stdout.write(f'Sent {count} expired notifications.')
