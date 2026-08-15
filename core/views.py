"""Public-site and error-page views."""
from django.core.cache import cache
from django.db import DatabaseError, connection
from django.db.models import Q
from django.http import JsonResponse
from django.shortcuts import render
from django.utils import timezone
from django.views.decorators.http import require_GET

from ads.models import Ad, AdStatus
from locations.models import City
from seo.policies import page_seo_context
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
        latest_ads = list(
            Ad.objects.filter(status=AdStatus.ACTIVE, deleted_at__isnull=True)
            .filter(Q(expires_at__isnull=True) | Q(expires_at__gt=timezone.now()))
            .select_related('category', 'city', 'province')
            .prefetch_related('images')
            .order_by('-is_featured', '-sort_at', '-published_at')[:8]
        )
        context = {
            'categories': list(
                Category.objects.filter(is_active=True, parent__isnull=True)
                .order_by('sort_order', 'title')[:8]
            ),
            'latest_ads': latest_ads,
            'location_links': list(
                City.objects.filter(is_active=True, ads__in=[ad.pk for ad in latest_ads])
                .distinct()
                .order_by('sort_order', 'name')[:12]
            ) if latest_ads else [],
        }
        cache.set(key, context, HOME_DATA_TIMEOUT)
    return render(request, 'home.html', context)



@require_GET
def healthcheck(request):
    """Provide a minimal database-aware liveness endpoint for hosting monitors."""
    try:
        with connection.cursor() as cursor:
            cursor.execute('SELECT 1')
            cursor.fetchone()
    except DatabaseError:
        response = JsonResponse({'status': 'unavailable'}, status=503)
    else:
        response = JsonResponse({'status': 'ok'})
    response['X-Robots-Tag'] = 'noindex, noarchive'
    response['Cache-Control'] = 'no-store'
    return response


def error_404(request, exception):
    return render(request, 'errors/404.html', {**page_seo_context(request, indexable=False)}, status=404)


def error_500(request):
    return render(request, 'errors/500.html', {**page_seo_context(request, indexable=False)}, status=500)
