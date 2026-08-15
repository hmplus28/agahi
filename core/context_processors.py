"""Template context processors for shared public-site data."""
from django.core.cache import cache

from .cache_utils import SITE_SETTINGS_CACHE_KEY
from .models import SiteSettings

SITE_SETTINGS_TIMEOUT = 3600


def site_settings(request):
    """Expose cached site settings while keeping canonical URLs request-specific."""
    settings = cache.get(SITE_SETTINGS_CACHE_KEY)
    if settings is None:
        settings = SiteSettings.objects.first()
        cache.set(SITE_SETTINGS_CACHE_KEY, settings, SITE_SETTINGS_TIMEOUT)
    return {
        'site_settings': settings,
        'canonical_url': request.build_absolute_uri(request.path),
    }
