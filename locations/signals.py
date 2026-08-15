"""Invalidate public taxonomy/location cache when location hierarchy changes."""
from django.db.models.signals import post_delete, post_save

from core.cache_utils import PUBLIC_TAXONOMY_VERSION_KEY, bump_version

from .models import City, Country, Province


def _invalidate_locations(**kwargs):
    bump_version(PUBLIC_TAXONOMY_VERSION_KEY)


for model in (Country, Province, City):
    post_save.connect(_invalidate_locations, sender=model, dispatch_uid=f'locations.invalidate-cache.save.{model.__name__}')
    post_delete.connect(_invalidate_locations, sender=model, dispatch_uid=f'locations.invalidate-cache.delete.{model.__name__}')
