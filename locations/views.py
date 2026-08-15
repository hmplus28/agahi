"""Location JSON helpers and SEO-facing server-rendered landing pages."""
from django.core.paginator import Paginator
from django.db.models import Q
from django.http import JsonResponse
from django.shortcuts import get_object_or_404, render
from django.utils import timezone
from django.views.decorators.http import require_GET

from ads.models import Ad, AdStatus
from seo.policies import (
    is_indexable_category_location,
    is_indexable_location,
    page_seo_context,
)
from taxonomy.models import Category

from .models import City, Province


@require_GET
def province_list(request):
    country_id = request.GET.get('country')
    provinces = Province.objects.filter(is_active=True)
    if country_id and country_id.isdigit():
        provinces = provinces.filter(country_id=country_id)
    return JsonResponse({'results': list(provinces.order_by('sort_order', 'name').values('id', 'name', 'slug'))})


@require_GET
def city_list(request):
    province_id = request.GET.get('province')
    cities = City.objects.filter(is_active=True)
    if province_id and province_id.isdigit():
        cities = cities.filter(province_id=province_id)
    return JsonResponse({'results': list(cities.order_by('sort_order', 'name').values('id', 'name', 'slug'))})


def _public_ads_for_city(city):
    return (
        Ad.objects.filter(status=AdStatus.ACTIVE, deleted_at__isnull=True, city=city)
        .filter(Q(expires_at__isnull=True) | Q(expires_at__gt=timezone.now()))
        .select_related('category', 'city', 'province')
        .prefetch_related('images')
    )


def _resolve_city(slug):
    """Root-level location URLs are reserved for active city slugs."""
    return get_object_or_404(City.objects.select_related('province'), slug=slug, is_active=True)


def location_detail(request, slug):
    """Render an inventory-backed city landing, never a thin auto-generated page."""
    city = _resolve_city(slug)
    ads = _public_ads_for_city(city)
    ad_count = ads.count()
    categories = (
        Category.objects.filter(is_active=True, ads__in=ads)
        .distinct()
        .order_by('sort_order', 'title')[:24]
    )
    page_obj = Paginator(ads.order_by('-is_featured', '-sort_at', '-published_at'), 24).get_page(request.GET.get('page'))
    intro = f'آگهی‌های معتبر شهر {city.name} در دسته‌بندی‌های متنوع را مشاهده و مقایسه کنید.'
    return render(request, 'locations/location_detail.html', {
        'city': city,
        'category': None,
        'categories': categories,
        'page_obj': page_obj,
        'ad_count': ad_count,
        'intro': intro,
        'breadcrumbs': [('خانه', '/'), (city.name, None)],
        **page_seo_context(
            request,
            indexable=is_indexable_location(city, ad_count),
            allow_pagination=True,
        ),
    })


def location_category_detail(request, location_slug, category_slug):
    """Render a city/category combination only when it contains real inventory."""
    city = _resolve_city(location_slug)
    category = get_object_or_404(Category.objects.filter(is_active=True), slug=category_slug)
    category_ids = [category.pk, *(item.pk for item in category.get_descendants() if item.is_active)]
    ads = _public_ads_for_city(city).filter(category_id__in=category_ids)
    ad_count = ads.count()
    page_obj = Paginator(ads.order_by('-is_featured', '-sort_at', '-published_at'), 24).get_page(request.GET.get('page'))
    intro = f'جدیدترین آگهی‌های {category.title} در {city.name} را در این صفحه مشاهده کنید.'
    return render(request, 'locations/location_detail.html', {
        'city': city,
        'category': category,
        'categories': [],
        'page_obj': page_obj,
        'ad_count': ad_count,
        'intro': intro,
        'breadcrumbs': [('خانه', '/'), (city.name, f'/{city.slug}/'), (category.title, None)],
        **page_seo_context(
            request,
            indexable=is_indexable_category_location(category, city, ad_count),
            allow_pagination=True,
        ),
    })
