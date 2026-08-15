"""Public-site and error-page views."""
from django.core.cache import cache
from django.shortcuts import render

from ads.models import Ad, AdStatus
from taxonomy.models import Category

from .cache_decorators import cache_public_page
from .cache_utils import cache_key, public_ads_version, public_taxonomy_version

HOME_DATA_TIMEOUT = 300


def _home_version():
    return f'{public_ads_version()}-{public_taxonomy_version()}'


@cache_public_page(60, _home_version, namespace='home-response')
def home(request):
    """Render a lightweight cached public homepage from server-side data."""
    version = _home_version()
    key = cache_key('home-data', version, 'default')
    context = cache.get(key)
    if context is None:
        context = {
            'categories': list(
                Category.objects.filter(is_active=True, parent__isnull=True)
                .order_by('sort_order', 'title')[:8]
            ),
            'latest_ads': list(
                Ad.objects.filter(status=AdStatus.ACTIVE, deleted_at__isnull=True)
                .select_related('category', 'city', 'province')
                .prefetch_related('images')
                .order_by('-is_featured', '-sort_at', '-published_at')[:8]
            ),
        }
        cache.set(key, context, HOME_DATA_TIMEOUT)
    return render(request, 'home.html', context)


def error_404(request, exception):
    return render(request, 'errors/404.html', status=404)


def error_500(request):
    return render(request, 'errors/500.html', status=500)
