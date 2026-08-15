"""Move eligible automatic-ladder advertisements to the top of listing order."""
from datetime import timedelta

from django.core.management.base import BaseCommand
from django.db.models import Q
from django.utils import timezone

from ads.models import Ad, AdStatus


class Command(BaseCommand):
    help = 'Apply automatic laddering to eligible active advertisements.'

    def add_arguments(self, parser):
        parser.add_argument('--days', type=int, default=7, help='Minimum days since last ladder.')
        parser.add_argument('--limit', type=int, default=100, help='Maximum advertisements to process.')

    def handle(self, *args, **options):
        now = timezone.now()
        cutoff = now - timedelta(days=max(options['days'], 1))
        candidates = (
            Ad.objects.filter(
                status=AdStatus.ACTIVE,
                auto_ladder=True,
                deleted_at__isnull=True,
            )
            .filter(Q(expires_at__isnull=True) | Q(expires_at__gt=now))
            .filter(Q(last_ladder_at__isnull=True) | Q(last_ladder_at__lte=cutoff))
            .order_by('last_ladder_at', 'sort_at')[:max(options['limit'], 1)]
        )
        ids = list(candidates.values_list('pk', flat=True))
        updated = Ad.objects.filter(pk__in=ids).update(sort_at=now, last_ladder_at=now)
        self.stdout.write(self.style.SUCCESS(f'{updated} آگهی نردبان خودکار دریافت کردند.'))
