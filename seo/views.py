"""SEO endpoints: crawl directives and scalable segmented XML sitemaps."""
from xml.sax.saxutils import escape

from django.core.cache import cache
from django.core.paginator import EmptyPage, Paginator
from django.db.models import Count, Q
from django.http import Http404, HttpResponse
from django.urls import reverse
from django.utils import timezone

from ads.models import Ad, AdStatus
from core.cache_utils import public_ads_version, public_taxonomy_version
from locations.models import City
from taxonomy.models import Category

from .policies import is_indexable_category, is_indexable_location

SITEMAP_CACHE_TIMEOUT = 300
SITEMAP_ADS_PAGE_SIZE = 5_000


def _xml_response(content):
    return HttpResponse(content, content_type='application/xml; charset=utf-8')


def _cache_key(request, section):
    return f'sitemap:{request.get_host()}:{public_ads_version()}:{public_taxonomy_version()}:{section}'


def _public_ad_queryset():
    return Ad.objects.filter(
        status=AdStatus.ACTIVE,
        deleted_at__isnull=True,
    ).filter(Q(expires_at__isnull=True) | Q(expires_at__gt=timezone.now()))


def _url_entry(url, last_modified=None):
    entry = [f'<url><loc>{escape(url)}</loc>']
    if last_modified:
        entry.append(f'<lastmod>{timezone.localtime(last_modified).date().isoformat()}</lastmod>')
    entry.append('</url>')
    return ''.join(entry)


def _urlset(entries):
    return (
        '<?xml version="1.0" encoding="UTF-8"?>'
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
        f'{"".join(entries)}</urlset>'
    )


def _sitemapindex(entries):
    return (
        '<?xml version="1.0" encoding="UTF-8"?>'
        '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
        f'{"".join(entries)}</sitemapindex>'
    )


def robots_txt(request):
    sitemap_url = request.build_absolute_uri(reverse('sitemap_xml'))
    lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin/',
        'Disallow: /dashboard/',
        'Disallow: /accounts/',
        'Disallow: /billing/',
        'Disallow: /support/',
        'Disallow: /notifications/',
        'Disallow: /moderation/',
        f'Sitemap: {sitemap_url}',
    ]
    return HttpResponse('\n'.join(lines) + '\n', content_type='text/plain; charset=utf-8')


def sitemap_xml(request):
    """Return a sitemap index; individual sections are cached independently."""
    cache_key = _cache_key(request, 'index')
    content = cache.get(cache_key)
    if content is None:
        ads_count = _public_ad_queryset().count()
        entries = [
            f'<sitemap><loc>{escape(request.build_absolute_uri(reverse("sitemap_categories")))}</loc></sitemap>',
            f'<sitemap><loc>{escape(request.build_absolute_uri(reverse("sitemap_locations")))}</loc></sitemap>',
        ]
        for page in range(1, (ads_count + SITEMAP_ADS_PAGE_SIZE - 1) // SITEMAP_ADS_PAGE_SIZE + 1):
            url = request.build_absolute_uri(reverse('sitemap_ads', kwargs={'page': page}))
            entries.append(f'<sitemap><loc>{escape(url)}</loc></sitemap>')
        content = _sitemapindex(entries)
        cache.set(cache_key, content, SITEMAP_CACHE_TIMEOUT)
    return _xml_response(content)


def sitemap_ads(request, page):
    """Return one bounded segment of indexable approved advertisements."""
    cache_key = _cache_key(request, f'ads:{page}')
    content = cache.get(cache_key)
    if content is None:
        paginator = Paginator(_public_ad_queryset().only('pk', 'code', 'slug', 'updated_at'), SITEMAP_ADS_PAGE_SIZE)
        try:
            ad_page = paginator.page(page)
        except EmptyPage as exc:
            raise Http404('بخش sitemap وجود ندارد.') from exc
        entries = [
            _url_entry(
                request.build_absolute_uri(ad.get_absolute_url()),
                ad.updated_at,
            )
            for ad in ad_page.object_list
        ]
        content = _urlset(entries)
        cache.set(cache_key, content, SITEMAP_CACHE_TIMEOUT)
    return _xml_response(content)


def sitemap_categories(request):
    """List only active category landings with at least one live advertisement."""
    cache_key = _cache_key(request, 'categories')
    content = cache.get(cache_key)
    if content is None:
        now = timezone.now()
        categories = Category.objects.filter(is_active=True).annotate(
            public_ad_count=Count(
                'ads',
                filter=Q(
                    ads__status=AdStatus.ACTIVE,
                    ads__deleted_at__isnull=True,
                ) & (Q(ads__expires_at__isnull=True) | Q(ads__expires_at__gt=now)),
            )
        ).only('pk', 'slug', 'updated_at', 'is_active')
        entries = [
            _url_entry(request.build_absolute_uri(category.get_absolute_url()), category.updated_at)
            for category in categories
            if is_indexable_category(category, category.public_ad_count)
        ]
        content = _urlset(entries)
        cache.set(cache_key, content, SITEMAP_CACHE_TIMEOUT)
    return _xml_response(content)


def sitemap_locations(request):
    """List only city landing pages that meet the conservative inventory threshold."""
    cache_key = _cache_key(request, 'locations')
    content = cache.get(cache_key)
    if content is None:
        now = timezone.now()
        cities = City.objects.filter(is_active=True).annotate(
            public_ad_count=Count(
                'ads',
                filter=Q(
                    ads__status=AdStatus.ACTIVE,
                    ads__deleted_at__isnull=True,
                ) & (Q(ads__expires_at__isnull=True) | Q(ads__expires_at__gt=now)),
            )
        ).only('pk', 'slug', 'is_active')
        entries = [
            _url_entry(request.build_absolute_uri(reverse('location_detail', kwargs={'slug': city.slug})))
            for city in cities
            if is_indexable_location(city, city.public_ad_count)
        ]
        content = _urlset(entries)
        cache.set(cache_key, content, SITEMAP_CACHE_TIMEOUT)
    return _xml_response(content)
