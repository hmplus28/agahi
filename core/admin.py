"""Django Admin for centrally managed non-secret site settings."""
from django.contrib import admin
from django.contrib import messages

from .models import SiteSettings


@admin.register(SiteSettings)
class SiteSettingsAdmin(admin.ModelAdmin):
    fieldsets = (
        ('اطلاعات سایت', {'fields': ('site_name', 'site_description', 'default_meta_description', 'logo', 'contact_phone', 'support_email')}),
        ('تنظیمات آگهی', {'fields': ('free_ad_duration', 'max_images', 'max_links')}),
        ('SEO و پیامک', {'fields': ('seo_threshold_low_views', 'sms_enabled', 'sms_sender_id')}),
        ('نگه‌داری', {'fields': ('maintenance_mode', 'maintenance_message')}),
    )
    readonly_fields = ('updated_at',)

    def has_add_permission(self, request):
        return not SiteSettings.objects.exists() and super().has_add_permission(request)

    def delete_model(self, request, obj):
        self.message_user(request, 'تنظیمات سایت قابل حذف نیستند.', messages.ERROR)

    def has_delete_permission(self, request, obj=None):
        return False
