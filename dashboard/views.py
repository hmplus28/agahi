"""
Dashboard views - User panel views.
"""
from django.shortcuts import render, redirect
from django.contrib.auth.decorators import login_required
from django.db.models import Count, Q
from ads.models import Ad
from billing.models import Order, Invoice
from support.models import Ticket


@login_required
def index(request):
    """Dashboard home page."""
    # User's ads count by status
    user_ads = Ad.objects.filter(owner=request.user)
    ads_stats = {
        'total': user_ads.count(),
        'active': user_ads.filter(status='active').count(),
        'pending': user_ads.filter(status='pending').count(),
        'expired': user_ads.filter(status='expired').count(),
    }
    
    # Recent ads
    recent_ads = user_ads.select_related('category', 'location_city').order_by('-created_at')[:5]
    
    # Recent orders
    recent_orders = Order.objects.filter(user=request.user).select_related('tariff').order_by('-created_at')[:5]
    
    # Open tickets
    open_tickets = Ticket.objects.filter(user=request.user, is_closed=False).count()
    
    context = {
        'ads_stats': ads_stats,
        'recent_ads': recent_ads,
        'recent_orders': recent_orders,
        'open_tickets': open_tickets,
    }
    
    return render(request, 'dashboard/index.html', context)


@login_required
def my_ads(request):
    """User's ads list."""
    ads = Ad.objects.filter(owner=request.user).select_related(
        'category', 'location_city'
    ).prefetch_related(
        'images'
    ).order_by('-created_at')
    
    # Filter by status
    status = request.GET.get('status')
    if status:
        ads = ads.filter(status=status)
    
    # Search
    q = request.GET.get('q')
    if q:
        ads = ads.filter(Q(title__icontains=q) | Q(description__icontains=q))
    
    context = {
        'ads': ads,
    }
    
    return render(request, 'dashboard/my_ads.html', context)


@login_required
def profile(request):
    """User profile page."""
    if request.method == 'POST':
        # Update user info
        request.user.first_name = request.POST.get('first_name', '')
        request.user.last_name = request.POST.get('last_name', '')
        request.user.save()
        
        # Update profile
        profile = request.user.profile
        profile.bio = request.POST.get('bio', '')
        profile.save()
        
        from django.contrib import messages
        messages.success(request, 'اطلاعات پروفایل با موفقیت به‌روزرسانی شد.')
        return redirect('dashboard:profile')
    
    context = {
        'user': request.user,
        'profile': request.user.profile,
    }
    
    return render(request, 'dashboard/profile.html', context)
