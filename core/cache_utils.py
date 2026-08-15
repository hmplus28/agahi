"""Small, versioned cache helpers for safe shared public data."""
from hashlib import sha256

from django.core.cache import cache

SITE_SETTINGS_CACHE_KEY = 'core:site-settings:v1'
PUBLIC_ADS_VERSION_KEY = 'ads:public-version'
PUBLIC_TAXONOMY_VERSION_KEY = 'taxonomy:public-version'


def _version(key):
    version = cache.get(key)
    if version is None:
        cache.add(key, 1, timeout=None)
        version = cache.get(key, 1)
    return version


def bump_version(key):
    """Invalidate a family of keys without requiring backend-specific delete_pattern."""
    try:
        cache.incr(key)
    except ValueError:
        cache.set(key, 2, timeout=None)


def public_ads_version():
    return _version(PUBLIC_ADS_VERSION_KEY)


def public_taxonomy_version():
    return _version(PUBLIC_TAXONOMY_VERSION_KEY)


def cache_key(namespace, version, *parts):
    material = ':'.join(str(part) for part in parts)
    digest = sha256(material.encode('utf-8')).hexdigest()[:24]
    return f'{namespace}:v{version}:{digest}'
