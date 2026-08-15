"""Operational Django Admin configuration for advertisements and moderation data."""
from django.contrib import admin, messages
from django.db import transaction
from django.db.models import Prefetch, Q

from billing.models import AdService
from core.services.persian_normalization import normalize_persian_text

from .models import Ad, AdImage, AdLink, AdPermit, AdReport, AdStatus, AdStatusHistory, ForbiddenWord
from .services.lifecycle import transition


class AdImageInline(admin.TabularInline):
    model = AdImage
    extra = 0
    fields = ('image_thumb', 'image_display', 'sort_order', 'is_primary', 'thumb_width', 'thumb_height')
    readonly_fields = ('thumb_width', 'thumb_height')


class AdLinkInline(admin.TabularInline):
    model = AdLink
    extra = 0
    fields = ('type', 'url', 'sort_order', 'is_active')


class AdStatusHistoryInline(admin.TabularInline):
    model = AdStatusHistory
    extra = 0
    can_delete = False
    fields = ('from_status', 'to_status', 'changed_by', 'reason', 'created_at')
    readonly_fields = fields


@admin.register(Ad)
class AdAdmin(admin.ModelAdmin):
    """Manage ad workflow through controlled actions instead of editable raw status fields."""
    list_display = (
        'code', 'title', 'status', 'user', 'city', 'views_count', 'service_summary',
        'published_at', 'expires_at', 'created_at',
    )
    list_filter = ('status', 'source', 'is_featured', 'is_urgent', 'is_colored', 'category', 'city')
    list_select_related = ('user', 'category', 'city')
    list_per_page = 100
    list_max_show_all = 400
    readonly_fields = (
        'code', 'slug', 'normalized_title', 'normalized_title_hash', 'normalized_description',
        'normalized_description_hash', 'created_at', 'updated_at', 'published_at', 'deleted_at',
        'views_count', 'last_ladder_at',
    )
    exclude = ('status',)
    date_hierarchy = 'created_at'
    actions = (
        'approve_selected', 'deactivate_selected', 'request_permit_selected',
        'soft_delete_selected', 'restore_selected',
    )
    inlines = (AdImageInline, AdLinkInline, AdStatusHistoryInline)

    def get_queryset(self, request):
        return super().get_queryset(request).prefetch_related(
            Prefetch('services', queryset=AdService.objects.filter(status='active').select_related('tariff'))
        )

    def get_search_results(self, request, queryset, search_term):
        """Use exact/prefix paths for public code and mobile, text search for text fields."""
        if not search_term:
            return queryset, False
        term = search_term.strip()
        if term.isdigit():
            return queryset.filter(
                Q(code__iexact=term)
                | Q(code__istartswith=term)
                | Q(mobile_1__startswith=term)
                | Q(mobile_2__startswith=term)
                | Q(user__mobile__startswith=term)
            ), False
        return queryset.filter(
            Q(title__icontains=term)
            | Q(business_name__icontains=term)
            | Q(description__icontains=term)
            | Q(city__name__icontains=term)
            | Q(user__first_name__icontains=term)
            | Q(user__last_name__icontains=term)
        ), False

    @admin.display(description='خدمات فعال')
    def service_summary(self, obj):
        return ', '.join(service.tariff.title for service in obj.services.all()[:3]) or '—'

    def _transition_selected(self, request, queryset, target, reason):
        changed = 0
        with transaction.atomic():
            for ad in queryset.select_for_update():
                if ad.status == target:
                    continue
                try:
                    transition(ad, target, changed_by=request.user, reason=reason)
                except Exception:
                    continue
                changed += 1
        if changed:
            self.message_user(request, f'{changed} آگهی به‌روزرسانی شد.', messages.SUCCESS)
        else:
            self.message_user(request, 'هیچ آگهی واجد شرایطی برای این عملیات نبود.', messages.WARNING)

    @admin.action(description='تأیید و فعال‌سازی آگهی‌های انتخاب‌شده')
    def approve_selected(self, request, queryset):
        self._transition_selected(request, queryset.filter(status__in=[AdStatus.PENDING_APPROVAL, AdStatus.NEEDS_PERMIT]), AdStatus.ACTIVE, 'تأیید توسط مدیر')

    @admin.action(description='غیرفعال‌سازی آگهی‌های انتخاب‌شده')
    def deactivate_selected(self, request, queryset):
        self._transition_selected(request, queryset.exclude(status=AdStatus.DELETED), AdStatus.INACTIVE, 'غیرفعال‌سازی توسط مدیر')

    @admin.action(description='درخواست مجوز برای آگهی‌های در انتظار تأیید')
    def request_permit_selected(self, request, queryset):
        self._transition_selected(request, queryset.filter(status=AdStatus.PENDING_APPROVAL), AdStatus.NEEDS_PERMIT, 'درخواست مجوز توسط مدیر')

    @admin.action(description='حذف منطقی آگهی‌های انتخاب‌شده')
    def soft_delete_selected(self, request, queryset):
        self._transition_selected(request, queryset.exclude(status=AdStatus.DELETED), AdStatus.DELETED, 'حذف منطقی توسط مدیر')

    @admin.action(description='بازگردانی آگهی‌های حذف‌شده به وضعیت غیرفعال')
    def restore_selected(self, request, queryset):
        self._transition_selected(request, queryset.filter(status=AdStatus.DELETED), AdStatus.INACTIVE, 'بازیابی توسط مدیر')


@admin.register(AdPermit)
class AdPermitAdmin(admin.ModelAdmin):
    list_display = ('ad', 'permit_number', 'issuer', 'status', 'created_at', 'updated_at')
    list_filter = ('status',)
    search_fields = ('ad__code', 'ad__title', 'permit_number', 'issuer')
    list_select_related = ('ad',)
    actions = ('approve_selected', 'reject_selected')
    readonly_fields = ('created_at', 'updated_at')

    @admin.action(description='تأیید مجوز و فعال‌سازی آگهی‌های انتخاب‌شده')
    def approve_selected(self, request, queryset):
        changed = 0
        with transaction.atomic():
            for permit in queryset.select_related('ad').select_for_update():
                permit.status = 'approved'
                permit.admin_note = permit.admin_note or 'تأیید توسط مدیر'
                permit.save(update_fields=['status', 'admin_note', 'updated_at'])
                if permit.ad.status == AdStatus.NEEDS_PERMIT:
                    transition(permit.ad, AdStatus.ACTIVE, changed_by=request.user, reason='تأیید مجوز')
                changed += 1
        self.message_user(request, f'{changed} مجوز تأیید شد.', messages.SUCCESS)

    @admin.action(description='رد مجوزهای انتخاب‌شده')
    def reject_selected(self, request, queryset):
        changed = queryset.update(status='rejected')
        self.message_user(request, f'{changed} مجوز رد شد.', messages.WARNING)


@admin.register(ForbiddenWord)
class ForbiddenWordAdmin(admin.ModelAdmin):
    list_display = ('word', 'normalized_word', 'is_active', 'created_at', 'updated_at')
    list_filter = ('is_active',)
    search_fields = ('word', 'normalized_word')
    readonly_fields = ('created_at', 'updated_at')

    def save_model(self, request, obj, form, change):
        obj.normalized_word = normalize_persian_text(obj.word)
        super().save_model(request, obj, form, change)


@admin.register(AdReport)
class AdReportAdmin(admin.ModelAdmin):
    list_display = ('ad', 'reason', 'status', 'reporter_user', 'reporter_ip', 'created_at', 'reviewed_at')
    list_filter = ('status', 'reason')
    search_fields = ('ad__code', 'ad__title', 'reporter_user__mobile', 'reporter_ip')
    list_select_related = ('ad', 'reporter_user')
    readonly_fields = ('created_at',)


@admin.register(AdImage)
class AdImageAdmin(admin.ModelAdmin):
    list_display = ('ad', 'sort_order', 'is_primary', 'thumb_width', 'thumb_height', 'created_at')
    list_filter = ('is_primary',)
    search_fields = ('ad__code', 'ad__title')
    list_select_related = ('ad',)


@admin.register(AdLink)
class AdLinkAdmin(admin.ModelAdmin):
    list_display = ('ad', 'type', 'url', 'is_active', 'sort_order', 'created_at')
    list_filter = ('type', 'is_active')
    search_fields = ('ad__code', 'ad__title', 'url')
    list_select_related = ('ad',)
