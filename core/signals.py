"""Cache invalidation signals for shared site data."""
from django.core.cache import cache
from django.db.models.signals import post_delete, post_save
from django.dispatch import receiver

from .cache_utils import SITE_SETTINGS_CACHE_KEY
from .models import SiteSettings


@receiver([post_save, post_delete], sender=SiteSettings)
def invalidate_site_settings_cache(**kwargs):
    cache.delete(SITE_SETTINGS_CACHE_KEY)
