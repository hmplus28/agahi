"""Public and authenticated advertisement views."""
from decimal import Decimal, InvalidOperation

from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import ValidationError
from django.core.paginator import Paginator
from django.db.models import F, Q
from django.shortcuts import get_object_or_404, redirect, render
from django.utils import timezone
from django.views.decorators.http import require_POST

from core.models import SiteSettings
from core.services.duplicate_detection import detect_duplicate_ads
from taxonomy.models import Category

from .forms import AdForm, AdImageForm
from .models import Ad, AdStatus
from .services.image_processing import save_ad_image
from .services.lifecycle import transition


def _public_ads():
    return (
        Ad.objects.filter(status=AdStatus.ACTIVE, deleted_at__isnull=True)
        .select_related('category', 'city', 'province')
        .prefetch_related('images')
    )


def _decimal_query(value):
    try:
        return Decimal(value)
    except (InvalidOperation, TypeError, ValueError):
        return None


def _client_ip(request):
    forwarded = request.META.get('HTTP_X_FORWARDED_FOR', '')
    return forwarded.split(',')[0].strip() if forwarded else request.META.get('REMOTE_ADDR')


def _save_images(ad, files):
    max_images = (SiteSettings.objects.only('max_images').first() or SiteSettings(max_images=5)).max_images
    existing_count = ad.images.count()
    if existing_count + len(files) > max_images:
        raise ValidationError(f'حداکثر {max_images} تصویر برای هر آگهی مجاز است.')
    for offset, uploaded_file in enumerate(files):
        save_ad_image(
            ad,
            uploaded_file,
            sort_order=existing_count + offset,
            is_primary=(existing_count + offset == 0),
        )


def ad_list(request):
    """Server-rendered, crawlable public listing with bounded filters."""
    ads = _public_ads()
    query = request.GET.get('q', '').strip()
    if query:
        ads = ads.filter(Q(title__icontains=query) | Q(description__icontains=query))
    category_id = request.GET.get('category')
    if category_id and category_id.isdigit():
        ads = ads.filter(category_id=category_id)
    city_id = request.GET.get('city')
    if city_id and city_id.isdigit():
        ads = ads.filter(city_id=city_id)
    min_price = _decimal_query(request.GET.get('min_price'))
    max_price = _decimal_query(request.GET.get('max_price'))
    if min_price is not None:
        ads = ads.filter(price__gte=min_price)
    if max_price is not None:
        ads = ads.filter(price__lte=max_price)

    sort = request.GET.get('sort', 'newest')
    ordering = {
        'newest': ['-is_featured', '-sort_at', '-published_at'],
        'price_low': ['price', '-published_at'],
        'price_high': ['-price', '-published_at'],
    }.get(sort, ['-is_featured', '-sort_at', '-published_at'])
    page_obj = Paginator(ads.order_by(*ordering), 24).get_page(request.GET.get('page'))
    context = {
        'page_obj': page_obj,
        'categories': Category.objects.filter(is_active=True, parent__isnull=True).order_by('sort_order', 'title'),
        'filters': request.GET,
        'query': query,
        'sort': sort,
    }
    return render(request, 'ads/ad_list.html', context)


def ad_detail(request, pk):
    """Render an active public ad and atomically count a deduplicated session view."""
    ad = get_object_or_404(
        Ad.objects.select_related('category', 'city', 'province', 'user').prefetch_related('images', 'links'),
        pk=pk,
        deleted_at__isnull=True,
    )
    if not ad.is_indexable() and not (request.user.is_staff or request.user == ad.user):
        messages.error(request, 'این آگهی در دسترس عمومی نیست.')
        return redirect('ads:ad_list')

    view_key = f'ad-viewed-{ad.pk}'
    if not request.session.get(view_key):
        Ad.objects.filter(pk=ad.pk).update(views_count=F('views_count') + 1)
        request.session[view_key] = timezone.now().isoformat()
        request.session.set_expiry(60 * 60)
        ad.refresh_from_db(fields=['views_count'])

    related_ads = _public_ads().filter(category=ad.category).exclude(pk=ad.pk)
    if ad.city_id:
        related_ads = related_ads.filter(city_id=ad.city_id)
    context = {
        'ad': ad,
        'related_ads': related_ads[:4],
        'seo_robots': 'index,follow,max-image-preview:large' if ad.is_indexable() else 'noindex,follow',
    }
    return render(request, 'ads/ad_detail.html', context)


@login_required
def ad_create(request):
    """Create a pending advertisement owned by the authenticated user."""
    if request.method == 'POST':
        form = AdForm(request.POST)
        image_form = AdImageForm(request.POST, request.FILES)
        if form.is_valid() and image_form.is_valid():
            ad = form.save(commit=False)
            ad.user = request.user
            ad.source = 'user_panel'
            ad.submit_ip = _client_ip(request)
            ad.status = AdStatus.DRAFT
            duplicates = detect_duplicate_ads(
                title=ad.title,
                description=ad.description,
                mobile=ad.mobile_1,
                category_id=ad.category_id,
                city_id=ad.city_id,
            )[1]
            ad.save()
            try:
                _save_images(ad, image_form.cleaned_data['images'])
            except ValidationError as exc:
                ad.delete()
                form.add_error(None, exc)
            else:
                transition(ad, AdStatus.PENDING_APPROVAL, changed_by=request.user, reason='ثبت توسط کاربر')
                if duplicates:
                    messages.warning(request, 'آگهی مشابهی برای بررسی مدیر شناسایی شد.')
                messages.success(request, 'آگهی ثبت شد و پس از بررسی منتشر می‌شود.')
                return redirect('dashboard:my_ads')
    else:
        form = AdForm()
        image_form = AdImageForm()
    return render(request, 'ads/ad_form.html', {'form': form, 'image_form': image_form, 'ad': None})


@login_required
def ad_edit(request, pk):
    """Edit an owned, non-expired ad and return active ads to moderation."""
    ad = get_object_or_404(Ad, pk=pk, user=request.user, deleted_at__isnull=True)
    if not ad.can_edit:
        messages.error(request, 'این آگهی در وضعیت فعلی قابل ویرایش نیست.')
        return redirect('dashboard:my_ads')
    if request.method == 'POST':
        form = AdForm(request.POST, instance=ad)
        image_form = AdImageForm(request.POST, request.FILES)
        if form.is_valid() and image_form.is_valid():
            ad = form.save()
            try:
                _save_images(ad, image_form.cleaned_data['images'])
            except ValidationError as exc:
                form.add_error(None, exc)
            else:
                if ad.status == AdStatus.ACTIVE:
                    transition(ad, AdStatus.PENDING_APPROVAL, changed_by=request.user, reason='ویرایش توسط کاربر')
                messages.success(request, 'تغییرات آگهی ذخیره شد.')
                return redirect('dashboard:my_ads')
    else:
        form = AdForm(instance=ad)
        image_form = AdImageForm()
    return render(request, 'ads/ad_form.html', {'form': form, 'image_form': image_form, 'ad': ad})


@login_required
@require_POST
def ad_delete(request, pk):
    """Soft-delete an owned advertisement through the lifecycle service."""
    ad = get_object_or_404(Ad, pk=pk, user=request.user, deleted_at__isnull=True)
    transition(ad, AdStatus.DELETED, changed_by=request.user, reason='حذف توسط کاربر')
    messages.success(request, 'آگهی حذف شد.')
    return redirect('dashboard:my_ads')
