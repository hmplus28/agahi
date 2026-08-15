"""Django Admin configuration for support tickets."""
from django.contrib import admin

from .models import Ticket, TicketMessage


class TicketMessageInline(admin.TabularInline):
    model = TicketMessage
    extra = 0
    readonly_fields = ('sender', 'message', 'created_at')
    can_delete = False


@admin.register(Ticket)
class TicketAdmin(admin.ModelAdmin):
    list_display = ('id', 'subject', 'user', 'status', 'priority', 'created_at', 'closed_at')
    list_filter = ('status', 'priority')
    search_fields = ('subject', 'user__mobile', 'user__first_name', 'user__last_name')
    list_select_related = ('user',)
    readonly_fields = ('user', 'created_at', 'updated_at', 'closed_at')
    inlines = (TicketMessageInline,)
    actions = ('close_selected', 'reopen_selected')

    @admin.action(description='بستن تیکت‌های انتخاب‌شده')
    def close_selected(self, request, queryset):
        for ticket in queryset.exclude(status='closed'):
            ticket.close()

    @admin.action(description='بازگشایی تیکت‌های انتخاب‌شده')
    def reopen_selected(self, request, queryset):
        queryset.filter(status='closed').update(status='open', closed_at=None)


@admin.register(TicketMessage)
class TicketMessageAdmin(admin.ModelAdmin):
    list_display = ('ticket', 'sender', 'created_at')
    search_fields = ('ticket__subject', 'sender__mobile', 'message')
    list_select_related = ('ticket', 'sender')
    readonly_fields = ('ticket', 'sender', 'message', 'created_at')
