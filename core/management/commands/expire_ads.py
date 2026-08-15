"""Expire active advertisements whose paid validity has ended."""
from django.core.management.base import BaseCommand
from django.utils import timezone

from ads.models import Ad, AdStatus
from ads.services.lifecycle import transition


class Command(BaseCommand):
    help = 'Mark active advertisements as expired once expires_at has passed.'

    def add_arguments(self, parser):
        parser.add_argument('--limit', type=int, default=500, help='Maximum advertisements to process.')

    def handle(self, *args, **options):
        now = timezone.now()
        candidates = (
            Ad.objects.filter(
                status=AdStatus.ACTIVE,
                deleted_at__isnull=True,
                expires_at__isnull=False,
                expires_at__lte=now,
            )
            .order_by('expires_at')[:max(options['limit'], 1)]
        )
        count = 0
        for ad in candidates:
            transition(ad, AdStatus.EXPIRED, reason='انقضای خودکار اعتبار آگهی')
            count += 1
        self.stdout.write(self.style.SUCCESS(f'{count} آگهی منقضی شد.'))
