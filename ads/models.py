import hashlib
from django.db import models
from django.conf import settings
from django.utils import timezone
from django.utils.text import slugify
from django.utils.translation import gettext_lazy as _
from django.core.validators import MinValueValidator, MaxLengthValidator

from taxonomy.models import Category
from locations.models import Country, Province, City


class AdStatus(models.TextChoices):
    """Ad status choices."""
    DRAFT = 'draft', _('Draft')
    PENDING_APPROVAL = 'pending_approval', _('Pending Approval')
    ACTIVE = 'active', _('Active')
    NEEDS_PERMIT = 'needs_permit', _('Needs Permit')
    INACTIVE = 'inactive', _('Inactive')
    EXPIRED = 'expired', _('Expired')
    DELETED = 'deleted', _('Deleted')


class AdSource(models.TextChoices):
    """Ad source choices."""
    USER_PANEL = 'user_panel', _('User Panel')
    PUBLIC_FORM = 'public_form', _('Public Form')
    ADMIN = 'admin', _('Admin')
    IMPORT = 'import', _('Import')


class Ad(models.Model):
    """
    Main Ad model.
    Contains all information about an advertisement.
    """
    # Identity
    code = models.CharField(_('code'), max_length=20, unique=True, editable=False)
    user = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name='ads',
        verbose_name=_('user'),
    )
    
    # Content
    title = models.CharField(_('title'), max_length=300)
    normalized_title = models.CharField(_('normalized title'), max_length=300, blank=True, db_index=True)
    normalized_title_hash = models.CharField(_('normalized title hash'), max_length=64, blank=True, db_index=True)
    description = models.TextField(_('description'), validators=[MaxLengthValidator(6000)])
    normalized_description = models.TextField(_('normalized description'), blank=True)
    normalized_description_hash = models.CharField(_('normalized description hash'), max_length=64, blank=True, db_index=True)
    keywords = models.CharField(_('keywords'), max_length=500, blank=True, help_text=_('Comma-separated keywords, max 11'))
    
    # Pricing
    price = models.DecimalField(
        _('price'),
        max_digits=15,
        decimal_places=0,
        blank=True,
        null=True,
        validators=[MinValueValidator(0)],
        help_text=_('Leave blank for "توافقی"')
    )
    
    # Contact Information
    full_name = models.CharField(_('full name'), max_length=255)
    business_name = models.CharField(_('business name'), max_length=255, blank=True)
    
    # Location
    country = models.ForeignKey(
        Country,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name='ads',
        verbose_name=_('country'),
    )
    province = models.ForeignKey(
        Province,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name='ads',
        verbose_name=_('province'),
    )
    city = models.ForeignKey(
        City,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name='ads',
        verbose_name=_('city'),
    )
    address = models.TextField(_('address'), blank=True)
    
    # Contact Numbers
    mobile_1 = models.CharField(_('mobile 1'), max_length=11)
    show_mobile_1 = models.BooleanField(_('show mobile 1'), default=True)
    mobile_2 = models.CharField(_('mobile 2'), max_length=11, blank=True)
    phone_1 = models.CharField(_('phone 1'), max_length=11, blank=True)
    phone_2 = models.CharField(_('phone 2'), max_length=11, blank=True)
    email = models.EmailField(_('email'), blank=True)
    
    # Classification
    category = models.ForeignKey(
        Category,
        on_delete=models.SET_NULL,
        null=True,
        related_name='ads',
        verbose_name=_('category'),
    )
    
    # Tracking
    referrer = models.CharField(_('referrer'), max_length=255, blank=True)
    source = models.CharField(
        _('source'),
        max_length=20,
        choices=AdSource.choices,
        default=AdSource.USER_PANEL,
    )
    submit_ip = models.GenericIPAddressField(_('submit IP'), null=True, blank=True)
    
    # Status & Timing
    status = models.CharField(
        _('status'),
        max_length=20,
        choices=AdStatus.choices,
        default=AdStatus.DRAFT,
        db_index=True,
    )
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    updated_at = models.DateTimeField(_('updated at'), auto_now=True)
    published_at = models.DateTimeField(_('published at'), null=True, blank=True, db_index=True)
    expires_at = models.DateTimeField(_('expires at'), null=True, blank=True, db_index=True)
    
    # Sorting & Features
    sort_at = models.DateTimeField(_('sort at'), default=timezone.now, db_index=True)
    last_ladder_at = models.DateTimeField(_('last ladder at'), null=True, blank=True)
    views_count = models.PositiveIntegerField(_('views count'), default=0)
    is_featured = models.BooleanField(_('is featured'), default=False, db_index=True)
    is_colored = models.BooleanField(_('is colored'), default=False)
    is_urgent = models.BooleanField(_('is urgent'), default=False)
    auto_ladder = models.BooleanField(_('auto ladder'), default=False)
    
    # Soft Delete
    deleted_at = models.DateTimeField(_('deleted at'), null=True, blank=True, db_index=True)
    
    # SEO
    slug = models.SlugField(_('slug'), max_length=300, unique=True, blank=True)
    
    class Meta:
        verbose_name = _('ad')
        verbose_name_plural = _('ads')
        ordering = ['-is_featured', '-sort_at', '-published_at']
        indexes = [
            models.Index(fields=['status', 'published_at']),
            models.Index(fields=['status', 'category']),
            models.Index(fields=['status', 'city']),
            models.Index(fields=['category', 'city', 'status']),
            models.Index(fields=['user', 'status']),
            models.Index(fields=['expires_at', 'status']),
            models.Index(fields=['is_featured', 'sort_at']),
            models.Index(fields=['slug']),
            models.Index(fields=['code']),
            models.Index(fields=['normalized_title']),
            models.Index(fields=['normalized_title_hash']),
            models.Index(fields=['normalized_description_hash']),
        ]
    
    def __str__(self):
        return f"{self.code} - {self.title}"
    
    def save(self, *args, **kwargs):
        from core.services.persian_normalization import normalize_persian_text

        self.title = normalize_persian_text(self.title)
        self.description = normalize_persian_text(self.description)
        self.normalized_title = self.title
        self.normalized_description = self.description
        self.normalized_title_hash = hashlib.sha256(self.normalized_title.encode('utf-8')).hexdigest()
        self.normalized_description_hash = hashlib.sha256(
            self.normalized_description.encode('utf-8')
        ).hexdigest()
        if not self.code:
            self.code = self._generate_code()
        if not self.slug:
            base_slug = slugify(self.title, allow_unicode=True)
            self.slug = f"{self.code}-{base_slug[:260]}" if base_slug else self.code
        super().save(*args, **kwargs)

    def _generate_code(self):
        """Generate a collision-resistant public identifier."""
        import secrets
        while True:
            code = str(secrets.randbelow(90000000) + 10000000)
            if not Ad.objects.filter(code=code).exists():
                return code

    def get_absolute_url(self):
        from django.urls import reverse
        return reverse('ads:ad_detail', kwargs={'pk': self.pk})
    
    @property
    def is_active(self):
        return self.status == AdStatus.ACTIVE
    
    @property
    def is_expired(self):
        if self.expires_at and timezone.now() > self.expires_at:
            return True
        return False
    
    @property
    def can_edit(self):
        """Check if ad can be edited by user."""
        return self.status in [AdStatus.DRAFT, AdStatus.PENDING_APPROVAL, AdStatus.ACTIVE] and not self.is_expired
    
    @property
    def can_renew(self):
        """Check if ad can be renewed."""
        return self.is_expired or self.status == AdStatus.EXPIRED
    
    def get_display_price(self):
        """Return display price or 'توافقی'."""
        if self.price:
            return f"{self.price:,}"
        return _('توافقی')
    
    def is_indexable(self):
        """Check whether the public page may be indexed."""
        return self.status == AdStatus.ACTIVE and not self.deleted_at and not self.is_expired


class AdStatusHistory(models.Model):
    """Track ad status changes."""
    ad = models.ForeignKey(
        Ad,
        on_delete=models.CASCADE,
        related_name='status_history',
        verbose_name=_('ad'),
    )
    from_status = models.CharField(_('from status'), max_length=20, choices=AdStatus.choices)
    to_status = models.CharField(_('to status'), max_length=20, choices=AdStatus.choices)
    changed_by = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        verbose_name=_('changed by'),
    )
    reason = models.TextField(_('reason'), blank=True)
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    
    class Meta:
        verbose_name = _('ad status history')
        verbose_name_plural = _('ad status history')
        ordering = ['-created_at']
    
    def __str__(self):
        return f"{self.ad.code}: {self.from_status} → {self.to_status}"


class AdImage(models.Model):
    """
    Ad images with optimized versions.
    Original is not stored, only thumbnail and display versions.
    """
    ad = models.ForeignKey(
        Ad,
        on_delete=models.CASCADE,
        related_name='images',
        verbose_name=_('ad'),
    )
    image_thumb = models.ImageField(
        _('thumbnail'),
        upload_to='ads/thumbs/',
        width_field='thumb_width',
        height_field='thumb_height',
    )
    image_display = models.ImageField(
        _('display image'),
        upload_to='ads/display/',
        width_field='display_width',
        height_field='display_height',
    )
    thumb_width = models.PositiveIntegerField(_('thumb width'), null=True, blank=True)
    thumb_height = models.PositiveIntegerField(_('thumb height'), null=True, blank=True)
    display_width = models.PositiveIntegerField(_('display width'), null=True, blank=True)
    display_height = models.PositiveIntegerField(_('display height'), null=True, blank=True)
    sort_order = models.PositiveIntegerField(_('sort order'), default=0)
    is_primary = models.BooleanField(_('is primary'), default=False)
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    
    class Meta:
        verbose_name = _('ad image')
        verbose_name_plural = _('ad images')
        ordering = ['sort_order', 'created_at']
    
    def __str__(self):
        return f"Image for {self.ad.code}"
    
    def save(self, *args, **kwargs):
        if self.is_primary:
            # Ensure only one primary image per ad
            AdImage.objects.filter(ad=self.ad, is_primary=True).update(is_primary=False)
        super().save(*args, **kwargs)


class AdLink(models.Model):
    """External links for ads."""
    LINK_TYPES = [
        ('website', _('Website')),
        ('instagram', _('Instagram')),
        ('telegram', _('Telegram')),
        ('whatsapp', _('WhatsApp')),
        ('other', _('Other')),
    ]
    
    ad = models.ForeignKey(
        Ad,
        on_delete=models.CASCADE,
        related_name='links',
        verbose_name=_('ad'),
    )
    type = models.CharField(_('type'), max_length=20, choices=LINK_TYPES, default='website')
    url = models.URLField(_('URL'), max_length=500)
    sort_order = models.PositiveIntegerField(_('sort order'), default=0)
    is_active = models.BooleanField(_('is active'), default=True)
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    
    class Meta:
        verbose_name = _('ad link')
        verbose_name_plural = _('ad links')
        ordering = ['sort_order', 'created_at']
    
    def __str__(self):
        return f"{self.get_type_display()} - {self.ad.code}"


class AdPermit(models.Model):
    """Permit documents for ads requiring authorization."""
    PERMIT_STATUS = [
        ('pending', _('Pending')),
        ('approved', _('Approved')),
        ('rejected', _('Rejected')),
    ]
    
    ad = models.OneToOneField(
        Ad,
        on_delete=models.CASCADE,
        related_name='permit',
        verbose_name=_('ad'),
    )
    permit_number = models.CharField(_('permit number'), max_length=100, blank=True)
    issuer = models.CharField(_('issuer'), max_length=255, blank=True)
    issued_at = models.DateField(_('issued at'), null=True, blank=True)
    image = models.ImageField(_('permit image'), upload_to='permits/')
    status = models.CharField(
        _('status'),
        max_length=20,
        choices=PERMIT_STATUS,
        default='pending',
    )
    admin_note = models.TextField(_('admin note'), blank=True)
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    updated_at = models.DateTimeField(_('updated at'), auto_now=True)
    
    class Meta:
        verbose_name = _('ad permit')
        verbose_name_plural = _('ad permits')
    
    def __str__(self):
        return f"Permit for {self.ad.code}"


class ForbiddenWord(models.Model):
    """Forbidden words that cannot be used in ads."""
    word = models.CharField(_('word'), max_length=100, unique=True)
    normalized_word = models.CharField(_('normalized word'), max_length=100, blank=True, db_index=True)
    is_active = models.BooleanField(_('is active'), default=True)
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    updated_at = models.DateTimeField(_('updated at'), auto_now=True)
    
    class Meta:
        verbose_name = _('forbidden word')
        verbose_name_plural = _('forbidden words')
        ordering = ['word']
    
    def __str__(self):
        return self.word


class AdReport(models.Model):
    """User reports for inappropriate ads."""
    REASON_CHOICES = [
        ('no_response', _('عدم پاسخگویی')),
        ('fraud', _('کلاهبرداری')),
        ('false_info', _('خلاف واقع')),
        ('inappropriate', _('محتوای غیرمجاز')),
        ('offensive', _('محتوای نامناسب')),
        ('price_gouging', _('گران‌فروشی')),
        ('other', _('سایر')),
    ]
    
    STATUS_CHOICES = [
        ('new', _('New')),
        ('reviewing', _('Reviewing')),
        ('resolved', _('Resolved')),
        ('rejected', _('Rejected')),
    ]
    
    ad = models.ForeignKey(
        Ad,
        on_delete=models.CASCADE,
        related_name='reports',
        verbose_name=_('ad'),
    )
    reason = models.CharField(_('reason'), max_length=20, choices=REASON_CHOICES)
    description = models.TextField(_('description'), blank=True)
    reporter_user = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        verbose_name=_('reporter'),
    )
    reporter_ip = models.GenericIPAddressField(_('reporter IP'), null=True, blank=True)
    status = models.CharField(
        _('status'),
        max_length=20,
        choices=STATUS_CHOICES,
        default='new',
    )
    admin_note = models.TextField(_('admin note'), blank=True)
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    reviewed_at = models.DateTimeField(_('reviewed at'), null=True, blank=True)
    
    class Meta:
        verbose_name = _('ad report')
        verbose_name_plural = _('ad reports')
        ordering = ['-created_at']
    
    def __str__(self):
        return f"Report for {self.ad.code}"
