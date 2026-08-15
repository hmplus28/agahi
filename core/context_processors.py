"""Template context processors for shared public-site data."""
from .models import SiteSettings


def site_settings(request):
    """Expose the singleton settings record without failing first-run pages."""
    return {
        'site_settings': SiteSettings.objects.first(),
        'canonical_url': request.build_absolute_uri(request.path),
    }
