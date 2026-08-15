"""Django Admin configuration for SMS delivery history."""
from django.contrib import admin

from .models import SMSLog


@admin.register(SMSLog)
class SMSLogAdmin(admin.ModelAdmin):
    list_display = ('id', 'type', 'mobile', 'user', 'ad', 'status', 'attempts', 'next_attempt_at', 'provider_id', 'sent_at', 'created_at')
    list_filter = ('type', 'status')
    search_fields = ('mobile', 'provider_id', 'ad__code', 'ad__title', 'user__mobile')
    list_select_related = ('user', 'ad')
    readonly_fields = ('user', 'ad', 'mobile', 'type', 'message', 'provider_id', 'status', 'response', 'attempts', 'last_attempt_at', 'next_attempt_at', 'sent_at', 'created_at')
