"""Invalidate public listing cache when advertisement content changes."""
from django.db.models.signals import post_delete, post_save
from django.dispatch import receiver

from core.cache_utils import PUBLIC_ADS_VERSION_KEY, bump_version

from .models import Ad, AdImage


def _invalidate_ads(**kwargs):
    bump_version(PUBLIC_ADS_VERSION_KEY)


post_save.connect(_invalidate_ads, sender=Ad, dispatch_uid='ads.invalidate-cache.save')
post_delete.connect(_invalidate_ads, sender=Ad, dispatch_uid='ads.invalidate-cache.delete')
post_save.connect(_invalidate_ads, sender=AdImage, dispatch_uid='adimages.invalidate-cache.save')
post_delete.connect(_invalidate_ads, sender=AdImage, dispatch_uid='adimages.invalidate-cache.delete')
