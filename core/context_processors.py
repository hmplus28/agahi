"""Template context processors for shared public-site data."""
from django.core.cache import cache

from seo.policies import PRIVATE_PATH_PREFIXES, page_seo_context, request_has_arbitrary_filters

from .cache_utils import SITE_SETTINGS_CACHE_KEY
from .models import SiteSettings

SITE_SETTINGS_TIMEOUT = 3600


def site_settings(request):
    """Expose settings and a safe default SEO context that public views may override."""
    settings_obj = cache.get(SITE_SETTINGS_CACHE_KEY)
    if settings_obj is None:
        settings_obj = SiteSettings.objects.first()
        cache.set(SITE_SETTINGS_CACHE_KEY, settings_obj, SITE_SETTINGS_TIMEOUT)
    default_indexable = not request.path.startswith(PRIVATE_PATH_PREFIXES) and not request_has_arbitrary_filters(request)
    return {
        'site_settings': settings_obj,
        **page_seo_context(request, indexable=default_indexable),
    }
