"""Django Admin configuration for tariffs, invoices and payment operations."""
from django.contrib import admin

from .models import AdService, Invoice, InvoiceItem, Order, OrderItem, Payment, Tariff


@admin.register(Tariff)
class TariffAdmin(admin.ModelAdmin):
    list_display = ('code', 'title', 'service_type', 'price', 'duration_days', 'is_active', 'sort_order')
    list_filter = ('service_type', 'is_active')
    search_fields = ('code', 'title', 'description')
    ordering = ('sort_order', 'title')
    readonly_fields = ('created_at', 'updated_at')


class InvoiceItemInline(admin.TabularInline):
    model = InvoiceItem
    extra = 0
    readonly_fields = ('title', 'quantity', 'unit_price', 'total_price', 'metadata')
    can_delete = False


@admin.register(Invoice)
class InvoiceAdmin(admin.ModelAdmin):
    list_display = ('invoice_number', 'user', 'ad', 'status', 'total', 'created_at', 'paid_at')
    list_filter = ('status',)
    search_fields = ('invoice_number', 'user__mobile', 'ad__code', 'ad__title')
    list_select_related = ('user', 'ad')
    readonly_fields = ('invoice_number', 'user', 'ad', 'subtotal', 'discount', 'total', 'created_at', 'paid_at')
    inlines = (InvoiceItemInline,)


@admin.register(Payment)
class PaymentAdmin(admin.ModelAdmin):
    list_display = ('id', 'user', 'ad', 'amount', 'method', 'status', 'gateway', 'reference_id', 'created_at')
    list_filter = ('status', 'method', 'gateway')
    search_fields = ('reference_id', 'authority', 'user__mobile', 'ad__code', 'invoice__invoice_number')
    list_select_related = ('user', 'ad', 'invoice')
    readonly_fields = ('user', 'ad', 'invoice', 'amount', 'method', 'gateway', 'authority', 'reference_id', 'paid_at', 'verified_at', 'created_at')
    actions = ('mark_manual_review',)

    @admin.action(description='قرار دادن در وضعیت نیازمند بررسی')
    def mark_manual_review(self, request, queryset):
        queryset.exclude(status='successful').update(status='manual_review')


class OrderItemInline(admin.TabularInline):
    model = OrderItem
    extra = 0
    readonly_fields = ('tariff', 'quantity', 'unit_price', 'total_price', 'metadata')
    can_delete = False


@admin.register(Order)
class OrderAdmin(admin.ModelAdmin):
    list_display = ('id', 'user', 'status', 'total_amount', 'created_at', 'updated_at')
    list_filter = ('status',)
    search_fields = ('id', 'user__mobile')
    list_select_related = ('user',)
    readonly_fields = ('user', 'total_amount', 'created_at', 'updated_at')
    inlines = (OrderItemInline,)


@admin.register(AdService)
class AdServiceAdmin(admin.ModelAdmin):
    list_display = ('ad', 'tariff', 'status', 'starts_at', 'expires_at', 'created_at')
    list_filter = ('status', 'tariff__service_type')
    search_fields = ('ad__code', 'ad__title', 'tariff__code', 'tariff__title')
    list_select_related = ('ad', 'tariff')
    readonly_fields = ('created_at',)
