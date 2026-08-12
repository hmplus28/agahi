from django.db import models
from django.conf import settings
from django.utils import timezone
from django.utils.translation import gettext_lazy as _
from django.core.validators import MinValueValidator


class Tariff(models.Model):
    """Tariff model for ad services."""
    SERVICE_TYPES = [
        ('annual_ad', _('Annual Ad')),
        ('annual_renewal', _('Annual Renewal')),
        ('featured', _('Featured')),
        ('colored_card', _('Colored Card')),
        ('urgent', _('Urgent')),
        ('extra_link', _('Extra Link')),
        ('extra_image', _('Extra Image')),
        ('ladder', _('Ladder')),
        ('auto_ladder', _('Automatic Ladder')),
    ]
    
    code = models.CharField(_('code'), max_length=50, unique=True)
    title = models.CharField(_('title'), max_length=255)
    description = models.TextField(_('description'), blank=True)
    price = models.DecimalField(
        _('price'),
        max_digits=12,
        decimal_places=0,
        validators=[MinValueValidator(0)],
    )
    service_type = models.CharField(
        _('service type'),
        max_length=50,
        choices=SERVICE_TYPES,
    )
    duration_days = models.PositiveIntegerField(
        _('duration (days)'),
        default=365,
        help_text=_('Service duration in days')
    )
    is_active = models.BooleanField(_('is active'), default=True)
    sort_order = models.PositiveIntegerField(_('sort order'), default=0)
    settings_json = models.JSONField(
        _('settings'),
        default=dict,
        blank=True,
        help_text=_('Additional tariff settings')
    )
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    updated_at = models.DateTimeField(_('updated at'), auto_now=True)
    
    class Meta:
        verbose_name = _('tariff')
        verbose_name_plural = _('tariffs')
        ordering = ['sort_order', 'title']
    
    def __str__(self):
        return f"{self.title} - {self.price:,}"


class Order(models.Model):
    """Order model for purchasing services."""
    STATUS_CHOICES = [
        ('pending', _('Pending')),
        ('completed', _('Completed')),
        ('cancelled', _('Cancelled')),
    ]
    
    user = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name='orders',
        verbose_name=_('user'),
    )
    status = models.CharField(
        _('status'),
        max_length=20,
        choices=STATUS_CHOICES,
        default='pending',
    )
    total_amount = models.DecimalField(
        _('total amount'),
        max_digits=12,
        decimal_places=0,
        default=0,
    )
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    updated_at = models.DateTimeField(_('updated at'), auto_now=True)
    
    class Meta:
        verbose_name = _('order')
        verbose_name_plural = _('orders')
        ordering = ['-created_at']
    
    def __str__(self):
        return f"Order #{self.id} - {self.user}"


class OrderItem(models.Model):
    """Order item model."""
    order = models.ForeignKey(
        Order,
        on_delete=models.CASCADE,
        related_name='items',
        verbose_name=_('order'),
    )
    tariff = models.ForeignKey(
        Tariff,
        on_delete=models.CASCADE,
        verbose_name=_('tariff'),
    )
    quantity = models.PositiveIntegerField(_('quantity'), default=1)
    unit_price = models.DecimalField(
        _('unit price'),
        max_digits=12,
        decimal_places=0,
    )
    total_price = models.DecimalField(
        _('total price'),
        max_digits=12,
        decimal_places=0,
    )
    metadata = models.JSONField(_('metadata'), default=dict, blank=True)
    
    class Meta:
        verbose_name = _('order item')
        verbose_name_plural = _('order items')
    
    def __str__(self):
        return f"{self.tariff.title} x {self.quantity}"


class Invoice(models.Model):
    """Invoice model."""
    STATUS_CHOICES = [
        ('pending', _('Pending')),
        ('paid', _('Paid')),
        ('cancelled', _('Cancelled')),
    ]
    
    invoice_number = models.CharField(_('invoice number'), max_length=50, unique=True)
    user = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name='invoices',
        verbose_name=_('user'),
    )
    ad = models.ForeignKey(
        'ads.Ad',
        on_delete=models.CASCADE,
        null=True,
        blank=True,
        related_name='invoices',
        verbose_name=_('ad'),
    )
    status = models.CharField(
        _('status'),
        max_length=20,
        choices=STATUS_CHOICES,
        default='pending',
    )
    subtotal = models.DecimalField(
        _('subtotal'),
        max_digits=12,
        decimal_places=0,
        default=0,
    )
    discount = models.DecimalField(
        _('discount'),
        max_digits=12,
        decimal_places=0,
        default=0,
    )
    total = models.DecimalField(
        _('total'),
        max_digits=12,
        decimal_places=0,
        default=0,
    )
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    paid_at = models.DateTimeField(_('paid at'), null=True, blank=True)
    
    class Meta:
        verbose_name = _('invoice')
        verbose_name_plural = _('invoices')
        ordering = ['-created_at']
    
    def __str__(self):
        return f"Invoice {self.invoice_number}"
    
    def save(self, *args, **kwargs):
        if not self.invoice_number:
            # Generate invoice number
            from datetime import datetime
            date_part = datetime.now().strftime('%Y%m%d')
            last_invoice = Invoice.objects.filter(
                invoice_number__startswith=f"INV-{date_part}-"
            ).order_by('-invoice_number').first()
            if last_invoice:
                last_num = int(last_invoice.invoice_number.split('-')[-1])
                new_num = last_num + 1
            else:
                new_num = 1
            self.invoice_number = f"INV-{date_part}-{new_num:04d}"
        
        self.total = self.subtotal - self.discount
        super().save(*args, **kwargs)


class InvoiceItem(models.Model):
    """Invoice item model with unlimited rows."""
    invoice = models.ForeignKey(
        Invoice,
        on_delete=models.CASCADE,
        related_name='items',
        verbose_name=_('invoice'),
    )
    title = models.CharField(_('title'), max_length=255)
    quantity = models.PositiveIntegerField(_('quantity'), default=1)
    unit_price = models.DecimalField(
        _('unit price'),
        max_digits=12,
        decimal_places=0,
    )
    total_price = models.DecimalField(
        _('total price'),
        max_digits=12,
        decimal_places=0,
    )
    metadata = models.JSONField(_('metadata'), default=dict, blank=True)
    
    class Meta:
        verbose_name = _('invoice item')
        verbose_name_plural = _('invoice items')
    
    def __str__(self):
        return f"{self.title} - {self.total_price:,}"


class Payment(models.Model):
    """Payment model for tracking transactions."""
    METHOD_CHOICES = [
        ('free', _('Free')),
        ('online', _('Online')),
        ('card_to_card', _('Card to Card')),
    ]
    
    STATUS_CHOICES = [
        ('pending', _('Pending')),
        ('successful', _('Successful')),
        ('failed', _('Failed')),
        ('cancelled', _('Cancelled')),
        ('manual_review', _('Manual Review')),
    ]
    
    user = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name='payments',
        verbose_name=_('user'),
    )
    ad = models.ForeignKey(
        'ads.Ad',
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name='payments',
        verbose_name=_('ad'),
    )
    invoice = models.ForeignKey(
        Invoice,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name='payments',
        verbose_name=_('invoice'),
    )
    amount = models.DecimalField(
        _('amount'),
        max_digits=12,
        decimal_places=0,
        validators=[MinValueValidator(0)],
    )
    method = models.CharField(
        _('method'),
        max_length=20,
        choices=METHOD_CHOICES,
        default='online',
    )
    status = models.CharField(
        _('status'),
        max_length=20,
        choices=STATUS_CHOICES,
        default='pending',
        db_index=True,
    )
    gateway = models.CharField(_('gateway'), max_length=50, blank=True)
    authority = models.CharField(_('authority'), max_length=100, blank=True)
    reference_id = models.CharField(_('reference ID'), max_length=100, blank=True)
    paid_at = models.DateTimeField(_('paid at'), null=True, blank=True)
    verified_at = models.DateTimeField(_('verified at'), null=True, blank=True)
    admin_note = models.TextField(_('admin note'), blank=True)
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    
    class Meta:
        verbose_name = _('payment')
        verbose_name_plural = _('payments')
        ordering = ['-created_at']
        indexes = [
            models.Index(fields=['status', 'created_at']),
            models.Index(fields=['user', 'status']),
            models.Index(fields=['ad', 'status']),
        ]
    
    def __str__(self):
        return f"Payment {self.id} - {self.amount:,} - {self.status}"
