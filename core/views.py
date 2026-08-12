"""
Core app views - Home page and error pages.
"""
from django.shortcuts import render
from taxonomy.models import Category
from ads.models import Ad


def home(request):
    """Home page view."""
    # Get main categories (level 0)
    categories = Category.objects.filter(level=0, is_active=True)[:8]
    
    # Get latest active ads
    latest_ads = Ad.objects.select_related(
        'category', 'location_city', 'owner'
    ).prefetch_related(
        'images'
    ).filter(
        status='active'
    ).order_by('-created_at')[:8]
    
    context = {
        'categories': categories,
        'latest_ads': latest_ads,
    }
    
    return render(request, 'home.html', context)


def error_404(request, exception):
    """Custom 404 error page."""
    return render(request, 'errors/404.html', status=404)


def error_500(request):
    """Custom 500 error page."""
    return render(request, 'errors/500.html', status=500)
