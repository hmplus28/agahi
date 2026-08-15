"""Public-site and error-page views."""
from django.shortcuts import render

from ads.models import Ad, AdStatus
from taxonomy.models import Category


def home(request):
    """Render a lightweight public homepage from server-side data."""
    categories = Category.objects.filter(is_active=True, parent__isnull=True).order_by('sort_order', 'title')[:8]
    latest_ads = (
        Ad.objects.filter(status=AdStatus.ACTIVE, deleted_at__isnull=True)
        .select_related('category', 'city', 'province')
        .prefetch_related('images')
        .order_by('-is_featured', '-sort_at', '-published_at')[:8]
    )
    return render(request, 'home.html', {'categories': categories, 'latest_ads': latest_ads})


def error_404(request, exception):
    return render(request, 'errors/404.html', status=404)


def error_500(request):
    return render(request, 'errors/500.html', status=500)
