"""Public category browsing views."""
from django.core.paginator import Paginator
from django.shortcuts import get_object_or_404, render

from ads.models import Ad, AdStatus
from .models import Category


def _category_queryset():
    return Category.objects.filter(is_active=True).select_related('parent')


class CategoryListView:
    """Compatibility wrapper kept as a class-based view for the URL contract."""

    @classmethod
    def as_view(cls):
        def view(request):
            categories = _category_queryset().filter(parent__isnull=True)
            return render(request, 'taxonomy/category_list.html', {'categories': categories})
        return view


class CategoryDetailView:
    """Render a category with server-side paginated, public ads."""

    @classmethod
    def as_view(cls):
        def view(request, pk, slug=None):
            category = get_object_or_404(_category_queryset(), pk=pk)
            descendants = category.get_descendants()
            category_ids = [category.pk, *[item.pk for item in descendants]]
            ads = (
                Ad.objects.filter(
                    status=AdStatus.ACTIVE,
                    deleted_at__isnull=True,
                    category_id__in=category_ids,
                )
                .select_related('category', 'city', 'province')
                .prefetch_related('images')
            )
            page_obj = Paginator(ads, 24).get_page(request.GET.get('page'))
            return render(
                request,
                'taxonomy/category_detail.html',
                {'category': category, 'page_obj': page_obj, 'breadcrumbs': category.get_full_path()},
            )
        return view
