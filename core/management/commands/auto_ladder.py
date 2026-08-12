"""
Management command to auto-ladder ads.
Moves old ads to the top of listings periodically.
"""
from django.core.management.base import BaseCommand
from django.utils import timezone
from datetime import timedelta
from ads.models import Ad


class Command(BaseCommand):
    help = 'Auto-ladder old ads to move them to the top of listings'

    def add_arguments(self, parser):
        parser.add_argument(
            '--days',
            type=int,
            default=7,
            help='Number of days since last ladder (default: 7)'
        )
        parser.add_argument(
            '--limit',
            type=int,
            default=100,
            help='Maximum number of ads to ladder (default: 100)'
        )

    def handle(self, *args, **options):
        days = options['days']
        limit = options['limit']
        
        now = timezone.now()
        cutoff_date = now - timedelta(days=days)
        
        # Find active ads that haven't been laddered recently
        candidates = Ad.objects.filter(
            status='active',
            created_at__lt=cutoff_date,
            ladder_at__lt=cutoff_date
        ).order_by('created_at')[:limit]
        
        count = candidates.count()
        
        if count == 0:
            self.stdout.write(self.style.SUCCESS('No ads need laddering.'))
            return
        
        # Update ladder_at to now
        updated = 0
        for ad in candidates:
            ad.ladder_at = now
            ad.save(update_fields=['ladder_at'])
            updated += 1
        
        self.stdout.write(
            self.style.SUCCESS(f'Successfully laddered {updated} ads.')
        )
