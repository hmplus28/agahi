"""Authenticated user dashboard views."""
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.paginator import Paginator
from django.db.models import Q
from django.shortcuts import redirect, render

from ads.models import Ad, AdStatus
from billing.models import Order
from accounts.models import Profile
from support.models import Ticket


@login_required
def index(request):
    user_ads = Ad.objects.filter(user=request.user)
    ads_stats = {
        'total': user_ads.count(),
        'active': user_ads.filter(status=AdStatus.ACTIVE).count(),
        'pending': user_ads.filter(status=AdStatus.PENDING_APPROVAL).count(),
        'expired': user_ads.filter(status=AdStatus.EXPIRED).count(),
    }
    context = {
        'ads_stats': ads_stats,
        'recent_ads': user_ads.select_related('category', 'city').order_by('-created_at')[:5],
        'recent_orders': Order.objects.filter(user=request.user).order_by('-created_at')[:5],
        'open_tickets': Ticket.objects.filter(user=request.user).exclude(status='closed').count(),
    }
    return render(request, 'dashboard/index.html', context)


@login_required
def my_ads(request):
    ads = (
        Ad.objects.filter(user=request.user)
        .select_related('category', 'city')
        .prefetch_related('images')
        .order_by('-created_at')
    )
    status = request.GET.get('status', '')
    valid_statuses = {choice for choice, _ in AdStatus.choices}
    if status in valid_statuses:
        ads = ads.filter(status=status)
    query = request.GET.get('q', '').strip()
    if query:
        ads = ads.filter(Q(title__icontains=query) | Q(description__icontains=query))
    page_obj = Paginator(ads, 20).get_page(request.GET.get('page'))
    return render(
        request,
        'dashboard/my_ads.html',
        {'page_obj': page_obj, 'status': status, 'query': query, 'status_choices': AdStatus.choices},
    )


@login_required
def profile(request):
    profile_obj, _ = Profile.objects.get_or_create(user=request.user)
    if request.method == 'POST':
        request.user.first_name = request.POST.get('first_name', '').strip()
        request.user.last_name = request.POST.get('last_name', '').strip()
        request.user.email = request.POST.get('email', '').strip()
        request.user.save(update_fields=['first_name', 'last_name', 'email'])
        profile_obj.business_name = request.POST.get('business_name', '').strip()
        profile_obj.address = request.POST.get('address', '').strip()
        profile_obj.postal_code = request.POST.get('postal_code', '').strip()
        profile_obj.save(update_fields=['business_name', 'address', 'postal_code', 'updated_at'])
        messages.success(request, 'اطلاعات پروفایل به‌روزرسانی شد.')
        return redirect('dashboard:profile')
    return render(request, 'dashboard/profile.html', {'profile': profile_obj})
