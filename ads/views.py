"""Public and authenticated advertisement views."""
from decimal import Decimal, InvalidOperation

from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.cache import cache
from django.core.exceptions import ValidationError
from django.core.paginator import Paginator
from django.db import transaction
from django.db.models import F
from django.shortcuts import get_object_or_404, redirect, render
from django.utils import timezone
from django.views.decorators.http import require_POST

from core.cache_decorators import cache_public_page
from core.cache_utils import cache_key, public_ads_version, public_taxonomy_version
from core.models import SiteSettings
from core.rate_limit import rate_limit
from seo.policies import page_seo_context
from taxonomy.models import Category

from .forms import AdForm, AdImageForm, AdLinkForm, AdPermitForm, AdReportForm
from .models import Ad, AdLink, AdPermit, AdReport, AdStatus
from .search import search_ads
from .services.image_processing import save_ad_image, save_permit_image
from .services.lifecycle import transition


def _public_ads():
    """The public collection never includes a deleted or time-expired advertisement."""
    return (
        Ad.objects.filter(
            status=AdStatus.ACTIVE,
            deleted_at__isnull=True,
        )
        .filter(expires_at__isnull=True) | Ad.objects.none()
    )


def _active_public_ads():
    """Provide an ORM-friendly public queryset while retaining optional expiry dates."""
    return (
        Ad.objects.filter(status=AdStatus.ACTIVE, deleted_at__isnull=True)
        .filter(expires_at__isnull=True)
        .select_related('category', 'city', 'province')
        .prefetch_related('images')
    )


def _public_listing_ads():
    """Public listings may include active ads whose explicit expiry is still in the future."""
    from django.db.models import Q

    return (
        Ad.objects.filter(status=AdStatus.ACTIVE, deleted_at__isnull=True)
        .filter(Q(expires_at__isnull=True) | Q(expires_at__gt=timezone.now()))
        .select_related('category', 'city', 'province')
        .prefetch_related('images')
    )


def _public_page_version():
    return f'{public_ads_version()}-{public_taxonomy_version()}'


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


def _send_changed_active_ad_to_moderation(ad, user, reason):
    """Re-moderate visible ads whenever an owner changes public information."""
    if ad.status == AdStatus.ACTIVE:
        transition(ad, AdStatus.PENDING_APPROVAL, changed_by=user, reason=reason)


@cache_public_page(60, _public_page_version, namespace='ads-list-response')
def ad_list(request):
    """Server-rendered public listing with cached anonymous responses and bounded filters."""
    ads = _public_listing_ads()
    query = request.GET.get('q', '').strip()
    ads, search_ordering = search_ads(ads, query)
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
    if search_ordering and sort == 'newest':
        ordering = [search_ordering, *ordering]
    page_obj = Paginator(ads.order_by(*ordering), 24).get_page(request.GET.get('page'))
    context = {
        'page_obj': page_obj,
        'categories': Category.objects.filter(is_active=True, parent__isnull=True).order_by('sort_order', 'title'),
        'filters': request.GET,
        'query': query,
        'sort': sort,
    }
    return render(request, 'ads/ad_list.html', context)


def _ad_detail_queryset():
    return Ad.objects.select_related('category', 'city', 'province', 'user').prefetch_related('images', 'links')


def ad_detail_by_code(request, code, slug):
    """Render the canonical code/slug URL, redirecting stale title slugs permanently."""
    ad = get_object_or_404(_ad_detail_queryset(), code=code, deleted_at__isnull=True)
    if slug != ad.slug:
        return redirect(ad.get_absolute_url(), permanent=True)
    return _render_ad_detail(request, ad)


def ad_detail(request, pk):
    """Redirect the legacy database-id path to the permanent public code/slug path."""
    ad = get_object_or_404(Ad.objects.only('code', 'slug'), pk=pk, deleted_at__isnull=True)
    return redirect(ad.get_absolute_url(), permanent=True)


def _render_ad_detail(request, ad):
    """Render an active or expired ad and atomically count a deduplicated session view."""
    is_expired = ad.status == AdStatus.EXPIRED or ad.is_expired
    public_status = ad.status in {AdStatus.ACTIVE, AdStatus.EXPIRED}
    is_owner_or_staff = request.user.is_authenticated and (request.user.is_staff or request.user == ad.user)
    if not public_status and not is_owner_or_staff:
        messages.error(request, 'این آگهی در دسترس عمومی نیست.')
        return redirect('ads:ad_list')

    if public_status:
        view_key = f'ad-viewed-{ad.pk}'
        if not request.session.get(view_key):
            Ad.objects.filter(pk=ad.pk).update(views_count=F('views_count') + 1)
            request.session[view_key] = timezone.now().isoformat()
            request.session.set_expiry(60 * 60)
            ad.refresh_from_db(fields=['views_count'])

    related_ads = []
    if not is_expired and ad.category_id:
        related_key = cache_key('related-ads', public_ads_version(), ad.pk, ad.category_id, ad.city_id)
        related_ads = cache.get(related_key)
        if related_ads is None:
            related_query = _public_listing_ads().filter(category=ad.category).exclude(pk=ad.pk)
            if ad.city_id:
                related_query = related_query.filter(city_id=ad.city_id)
            related_ads = list(related_query.order_by('-is_featured', '-sort_at', '-published_at')[:4])
            cache.set(related_key, related_ads, 300)
    context = {
        'ad': ad,
        'related_ads': related_ads[:4],
        'is_expired_ad': is_expired,
        'mask_contact': is_expired and not is_owner_or_staff,
        'report_form': AdReportForm(),
        **page_seo_context(request, indexable=ad.is_indexable()),
    }
    return render(request, 'ads/ad_detail.html', context)


@require_POST
@rate_limit('ad-report', limit=5, period=3600)
def ad_report_create(request, pk):
    """Allow visitors to report public content with bounded abuse protection."""
    ad = get_object_or_404(
        Ad.objects.filter(deleted_at__isnull=True, status__in=[AdStatus.ACTIVE, AdStatus.EXPIRED]),
        pk=pk,
    )
    form = AdReportForm(request.POST)
    if form.is_valid():
        AdReport.objects.create(
            ad=ad,
            reason=form.cleaned_data['reason'],
            description=form.cleaned_data['description'],
            reporter_user=request.user if request.user.is_authenticated else None,
            reporter_ip=_client_ip(request),
        )
        messages.success(request, 'گزارش شما ثبت شد و بررسی می‌شود.')
    else:
        messages.error(request, 'گزارش ثبت نشد. دلیل گزارش را بررسی کنید.')
    return redirect(ad.get_absolute_url())


@login_required
def ad_create(request):
    """Create a pending advertisement owned by the authenticated user."""
    if request.method == 'POST':
        form = AdForm(request.POST)
        image_form = AdImageForm(request.POST, request.FILES)
        if form.is_valid() and image_form.is_valid():
            try:
                with transaction.atomic():
                    ad = form.save(commit=False)
                    ad.user = request.user
                    ad.source = 'user_panel'
                    ad.submit_ip = _client_ip(request)
                    ad.status = AdStatus.DRAFT
                    ad.save()
                    _save_images(ad, image_form.cleaned_data['images'])
                    transition(ad, AdStatus.PENDING_APPROVAL, changed_by=request.user, reason='ثبت توسط کاربر')
            except ValidationError as exc:
                form.add_error(None, exc)
            else:
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
            try:
                with transaction.atomic():
                    ad = form.save()
                    _save_images(ad, image_form.cleaned_data['images'])
                    _send_changed_active_ad_to_moderation(ad, request.user, 'ویرایش توسط کاربر')
            except ValidationError as exc:
                form.add_error(None, exc)
            else:
                messages.success(request, 'تغییرات آگهی ذخیره شد.')
                return redirect('dashboard:my_ads')
    else:
        form = AdForm(instance=ad)
        image_form = AdImageForm()
    return render(request, 'ads/ad_form.html', {'form': form, 'image_form': image_form, 'ad': ad})


@login_required
def ad_permit_upload(request, pk):
    """Let an owner submit an optimized permit document for staff review."""
    ad = get_object_or_404(Ad, pk=pk, user=request.user, deleted_at__isnull=True)
    if ad.status not in {AdStatus.NEEDS_PERMIT, AdStatus.EXPIRED}:
        messages.error(request, 'در وضعیت فعلی نیازی به ارسال مجوز برای این آگهی نیست.')
        return redirect('dashboard:my_ads')
    if request.method == 'POST':
        form = AdPermitForm(request.POST, request.FILES)
        if form.is_valid():
            try:
                optimized_image = save_permit_image(ad, form.cleaned_data['image'])
            except ValidationError as exc:
                form.add_error('image', exc)
            else:
                permit, _created = AdPermit.objects.get_or_create(ad=ad)
                permit.permit_number = form.cleaned_data['permit_number']
                permit.issuer = form.cleaned_data['issuer']
                permit.issued_at = form.cleaned_data['issued_at']
                permit.image.save(optimized_image.name, optimized_image, save=False)
                permit.status = 'pending'
                permit.admin_note = ''
                permit.save()
                messages.success(request, 'مجوز ارسال شد و پس از بررسی نتیجه اعلام می‌شود.')
                return redirect('dashboard:my_ads')
    else:
        permit = getattr(ad, 'permit', None)
        form = AdPermitForm(initial={
            'permit_number': getattr(permit, 'permit_number', ''),
            'issuer': getattr(permit, 'issuer', ''),
            'issued_at': getattr(permit, 'issued_at', None),
        })
    return render(request, 'ads/ad_permit_form.html', {'form': form, 'ad': ad})


@login_required
def ad_links(request, pk):
    """Manage bounded user-generated links for a non-expired owned advertisement."""
    ad = get_object_or_404(Ad.objects.prefetch_related('links'), pk=pk, user=request.user, deleted_at__isnull=True)
    if not ad.can_edit:
        messages.error(request, 'ویرایش لینک‌های این آگهی در وضعیت فعلی مجاز نیست.')
        return redirect('dashboard:my_ads')
    if request.method == 'POST':
        form = AdLinkForm(request.POST)
        if form.is_valid():
            if not form.cleaned_data.get('url'):
                form.add_error('url', 'نشانی لینک را وارد کنید.')
            else:
                max_links = (SiteSettings.objects.only('max_links').first() or SiteSettings(max_links=5)).max_links
                if ad.links.filter(is_active=True).count() >= max_links:
                    form.add_error(None, f'حداکثر {max_links} لینک برای هر آگهی مجاز است.')
                else:
                    AdLink.objects.create(
                        ad=ad,
                        type=form.cleaned_data['type'],
                        url=form.cleaned_data['url'],
                        sort_order=ad.links.count(),
                    )
                    _send_changed_active_ad_to_moderation(ad, request.user, 'ویرایش لینک‌های آگهی توسط کاربر')
                    messages.success(request, 'لینک افزوده شد.')
                    return redirect('ads:ad_links', pk=ad.pk)
    else:
        form = AdLinkForm()
    return render(request, 'ads/ad_links.html', {'form': form, 'ad': ad})


@login_required
@require_POST
def ad_link_delete(request, pk, link_id):
    """Remove one owned ad link and remoderate an active advertisement."""
    ad = get_object_or_404(Ad, pk=pk, user=request.user, deleted_at__isnull=True)
    if not ad.can_edit:
        messages.error(request, 'حذف لینک در وضعیت فعلی مجاز نیست.')
        return redirect('dashboard:my_ads')
    link = get_object_or_404(AdLink, pk=link_id, ad=ad)
    link.delete()
    _send_changed_active_ad_to_moderation(ad, request.user, 'حذف لینک آگهی توسط کاربر')
    messages.success(request, 'لینک حذف شد.')
    return redirect('ads:ad_links', pk=ad.pk)


@login_required
@require_POST
def ad_delete(request, pk):
    """Soft-delete an owned advertisement through the lifecycle service."""
    ad = get_object_or_404(Ad, pk=pk, user=request.user, deleted_at__isnull=True)
    transition(ad, AdStatus.DELETED, changed_by=request.user, reason='حذف توسط کاربر')
    messages.success(request, 'آگهی حذف شد.')
    return redirect('dashboard:my_ads')
