"""Public SEO endpoints."""
from xml.sax.saxutils import escape

from django.http import HttpResponse
from django.urls import reverse
from django.utils import timezone

from ads.models import Ad, AdStatus
from taxonomy.models import Category


def robots_txt(request):
    sitemap_url = request.build_absolute_uri(reverse('sitemap_xml'))
    lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin/',
        'Disallow: /dashboard/',
        'Disallow: /accounts/',
        'Disallow: /billing/',
        f'Sitemap: {sitemap_url}',
    ]
    return HttpResponse('\n'.join(lines) + '\n', content_type='text/plain; charset=utf-8')


def sitemap_xml(request):
    """Build a small dynamic sitemap for public indexable content."""
    urls = [(reverse('core:home'), None, 'daily', '1.0')]
    for category in Category.objects.filter(is_active=True):
        urls.append((
            reverse('taxonomy:category_detail_slug', kwargs={'pk': category.pk, 'slug': category.slug}),
            category.updated_at,
            'weekly',
            '0.8',
        ))
    for ad in Ad.objects.filter(status=AdStatus.ACTIVE, deleted_at__isnull=True):
        urls.append((
            reverse('ads:ad_detail', kwargs={'pk': ad.pk}),
            ad.updated_at,
            'weekly',
            '0.7',
        ))

    entries = []
    for path, last_modified, changefreq, priority in urls:
        location = escape(request.build_absolute_uri(path))
        entry = [f'<url><loc>{location}</loc>']
        if last_modified:
            entry.append(f'<lastmod>{timezone.localtime(last_modified).date().isoformat()}</lastmod>')
        entry.extend([f'<changefreq>{changefreq}</changefreq>', f'<priority>{priority}</priority></url>'])
        entries.append(''.join(entry))

    content = '<?xml version="1.0" encoding="UTF-8"?>' + (
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
        + ''.join(entries)
        + '</urlset>'
    )
    return HttpResponse(content, content_type='application/xml; charset=utf-8')
