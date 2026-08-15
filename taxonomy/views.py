"""Public category browsing views."""
from django.core.paginator import Paginator
from django.db.models import Q
from django.shortcuts import get_object_or_404, redirect, render
from django.utils import timezone

from ads.models import Ad, AdStatus
from core.cache_decorators import cache_public_page
from core.cache_utils import public_ads_version, public_taxonomy_version
from locations.models import City
from seo.policies import is_indexable_category, page_seo_context

from .models import Category


def _taxonomy_version():
    return f'{public_ads_version()}-{public_taxonomy_version()}'


def _category_queryset():
    return Category.objects.filter(is_active=True).select_related('parent')


class CategoryListView:
    """Render a cached, server-side category index."""

    @classmethod
    def as_view(cls):
        @cache_public_page(300, _taxonomy_version, namespace='category-list-response')
        def view(request):
            categories = _category_queryset().filter(parent__isnull=True).order_by('sort_order', 'title')
            return render(request, 'taxonomy/category_list.html', {'categories': categories})
        return view


class CategoryDetailView:
    """Render a cached category page with server-side paginated public ads."""

    @classmethod
    def as_view(cls):
        @cache_public_page(60, _taxonomy_version, namespace='category-detail-response')
        def view(request, pk, slug=None):
            category = get_object_or_404(_category_queryset(), pk=pk)
            if slug is not None and slug != category.slug:
                return redirect(category.get_absolute_url(), permanent=True)
            descendants = category.get_descendants()
            category_ids = [category.pk, *[item.pk for item in descendants if item.is_active]]
            ads = (
                Ad.objects.filter(
                    status=AdStatus.ACTIVE,
                    deleted_at__isnull=True,
                    category_id__in=category_ids,
                )
                .filter(Q(expires_at__isnull=True) | Q(expires_at__gt=timezone.now()))
                .select_related('category', 'city', 'province')
                .prefetch_related('images')
                .order_by('-is_featured', '-sort_at', '-published_at')
            )
            page_obj = Paginator(ads, 24).get_page(request.GET.get('page'))
            city_links = City.objects.filter(is_active=True, ads__in=ads).distinct().order_by('sort_order', 'name')[:16]
            intro = category.description or f'جدیدترین آگهی‌های {category.title} را در دسته‌بندی‌های معتبر مشاهده کنید.'
            return render(
                request,
                'taxonomy/category_detail.html',
                {
                    'category': category,
                    'page_obj': page_obj,
                    'breadcrumbs': category.get_full_path(),
                    'subcategories': category.children.filter(is_active=True).order_by('sort_order', 'title'),
                    'city_links': city_links,
                    'intro': intro,
                    **page_seo_context(
                        request,
                        indexable=is_indexable_category(category, page_obj.paginator.count),
                        path=category.get_absolute_url(),
                        allow_pagination=True,
                    ),
                },
            )
        return view
