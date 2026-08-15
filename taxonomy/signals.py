"""Invalidate public category cache when taxonomy changes."""
from django.db.models.signals import post_delete, post_save

from core.cache_utils import PUBLIC_TAXONOMY_VERSION_KEY, bump_version

from .models import Category


def _invalidate_taxonomy(**kwargs):
    bump_version(PUBLIC_TAXONOMY_VERSION_KEY)


post_save.connect(_invalidate_taxonomy, sender=Category, dispatch_uid='taxonomy.invalidate-cache.save')
post_delete.connect(_invalidate_taxonomy, sender=Category, dispatch_uid='taxonomy.invalidate-cache.delete')
