"""
Ads app views - Ad listing, creation, and management.
"""
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from django.db.models import Q
from django.core.paginator import Paginator
from .models import Ad, AdImage, Category
from .forms import AdForm, AdImageForm
from core.services.duplicate_detection import detect_duplicate_ads
from core.services.persian_normalization import normalize_persian_text


def ad_list(request):
    """Ad listing page with search and filters."""
    ads = Ad.objects.select_related(
        'category', 'location_city', 'owner'
    ).prefetch_related(
        'images'
    ).filter(status='active')
    
    # Search
    q = request.GET.get('q')
    if q:
        ads = ads.filter(Q(title__icontains=q) | Q(description__icontains=q))
    
    # Filter by category
    category_id = request.GET.get('category')
    if category_id:
        ads = ads.filter(category_id=category_id)
    
    # Filter by location
    city_id = request.GET.get('city')
    if city_id:
        ads = ads.filter(location_city_id=city_id)
    
    # Filter by price range
    min_price = request.GET.get('min_price')
    max_price = request.GET.get('max_price')
    if min_price:
        ads = ads.filter(price__gte=min_price)
    if max_price:
        ads = ads.filter(price__lte=max_price)
    
    # Sorting
    sort = request.GET.get('sort', '-created_at')
    if sort in ['created_at', '-created_at', 'price', '-price', 'title', '-title']:
        ads = ads.order_by(sort)
    
    # Pagination
    paginator = Paginator(ads, 12)
    page_number = request.GET.get('page')
    page_obj = paginator.get_page(page_number)
    
    # Get categories for filter
    categories = Category.objects.filter(is_active=True, level=0)
    
    context = {
        'page_obj': page_obj,
        'categories': categories,
        'filters': request.GET.dict(),
    }
    
    return render(request, 'ads/ad_list.html', context)


def ad_detail(request, pk):
    """Ad detail page."""
    ad = get_object_or_404(
        Ad.objects.select_related(
            'category', 'location_city', 'owner'
        ).prefetch_related('images', 'links'),
        pk=pk
    )
    
    # Check if ad is active
    if ad.status != 'active' and not request.user.is_staff:
        messages.error(request, 'این آگهی فعال نیست.')
        return redirect('ads:ad_list')
    
    # Related ads
    related_ads = Ad.objects.filter(
        category=ad.category,
        status='active',
        location_city=ad.location_city
    ).exclude(pk=ad.pk)[:4]
    
    context = {
        'ad': ad,
        'related_ads': related_ads,
    }
    
    return render(request, 'ads/ad_detail.html', context)


@login_required
def ad_create(request):
    """Create new ad."""
    if request.method == 'POST':
        form = AdForm(request.POST, request.FILES)
        image_form = AdImageForm(request.POST, request.FILES)
        
        if form.is_valid():
            ad = form.save(commit=False)
            ad.owner = request.user
            
            # Normalize Persian text
            ad.title = normalize_persian_text(ad.title)
            ad.description = normalize_persian_text(ad.description)
            
            # Check for duplicates
            is_duplicate, duplicates = detect_duplicate_ads(
                title=ad.title,
                description=ad.description or '',
                phone=ad.phone or '',
                category_id=ad.category.id,
                location_city_id=ad.location_city.id if ad.location_city else None,
            )
            
            if is_duplicate:
                messages.warning(
                    request,
                    'آگهی مشابهی قباً ثبت شده است. لطفاً از تکراری نبودن آگهی اطمینان حاصل کنید.'
                )
            
            ad.save()
            
            # Save images
            files = request.FILES.getlist('images')
            for file in files:
                AdImage.objects.create(ad=ad, image=file)
            
            messages.success(request, 'آگهی شما با موفقیت ثبت شد و پس از بررسی منتشر می‌شود.')
            return redirect('dashboard:my_ads')
    else:
        form = AdForm()
        image_form = AdImageForm()
    
    context = {
        'form': form,
        'image_form': image_form,
    }
    
    return render(request, 'ads/ad_form.html', context)


@login_required
def ad_edit(request, pk):
    """Edit existing ad."""
    ad = get_object_or_404(Ad, pk=pk, owner=request.user)
    
    if request.method == 'POST':
        form = AdForm(request.POST, request.FILES, instance=ad)
        
        if form.is_valid():
            ad = form.save(commit=False)
            ad.title = normalize_persian_text(ad.title)
            ad.description = normalize_persian_text(ad.description)
            ad.save()
            
            # Save new images
            files = request.FILES.getlist('images')
            for file in files:
                AdImage.objects.create(ad=ad, image=file)
            
            messages.success(request, 'آگهی با موفقیت به‌روزرسانی شد.')
            return redirect('dashboard:my_ads')
    else:
        form = AdForm(instance=ad)
    
    context = {
        'form': form,
        'ad': ad,
    }
    
    return render(request, 'ads/ad_form.html', context)


@login_required
def ad_delete(request, pk):
    """Delete ad."""
    ad = get_object_or_404(Ad, pk=pk, owner=request.user)
    
    if request.method == 'POST':
        ad.delete()
        messages.success(request, 'آگهی با موفقیت حذف شد.')
        return redirect('dashboard:my_ads')
    
    context = {'ad': ad}
    return render(request, 'ads/ad_confirm_delete.html', context)
