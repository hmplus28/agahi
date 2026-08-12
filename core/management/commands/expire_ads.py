"""
Management command to expire old ads.
Marks ads as expired if their expiration date has passed.
"""
from django.core.management.base import BaseCommand
from django.utils import timezone
from ads.models import Ad


class Command(BaseCommand):
    help = 'Mark ads as expired if their expiration date has passed'

    def handle(self, *args, **options):
        now = timezone.now()
        
        # Find active ads that have expired
        expired_ads = Ad.objects.filter(
            status='active',
            expires_at__lt=now
        )
        
        count = expired_ads.count()
        
        if count == 0:
            self.stdout.write(self.style.SUCCESS('No expired ads found.'))
            return
        
        # Update status to expired
        updated = expired_ads.update(status='expired')
        
        self.stdout.write(
            self.style.SUCCESS(f'Successfully marked {updated} ads as expired.')
        )
