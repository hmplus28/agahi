from django.db import models
from django.utils.translation import gettext_lazy as _


class SiteSettings(models.Model):
    """
    Singleton site settings model.
    Contains general configuration for the website.
    """
    site_name = models.CharField(_('site name'), max_length=255, default='سامانه آگهی')
    site_description = models.TextField(_('site description'), blank=True)
    default_meta_description = models.TextField(_('default meta description'), blank=True)
    logo = models.ImageField(_('logo'), upload_to='settings/', blank=True)
    contact_phone = models.CharField(_('contact phone'), max_length=20, blank=True)
    support_email = models.EmailField(_('support email'), blank=True)
    
    # Ad Settings
    free_ad_duration = models.PositiveIntegerField(
        _('free ad duration (days)'),
        default=30,
        help_text=_('Default duration for free ads in days')
    )
    max_images = models.PositiveIntegerField(
        _('max images per ad'),
        default=5,
    )
    max_links = models.PositiveIntegerField(
        _('max links per ad'),
        default=5,
    )
    
    # SEO Settings
    seo_threshold_low_views = models.PositiveIntegerField(
        _('SEO threshold for low views'),
        default=10,
        help_text=_('Ads with views below this are considered low-view')
    )
    
    # SMS Settings (non-sensitive)
    sms_enabled = models.BooleanField(_('SMS enabled'), default=False)
    sms_sender_id = models.CharField(_('SMS sender ID'), max_length=20, blank=True)
    
    # Maintenance
    maintenance_mode = models.BooleanField(_('maintenance mode'), default=False)
    maintenance_message = models.TextField(_('maintenance message'), blank=True)
    
    # Header/Footer
    header_html = models.TextField(_('header HTML'), blank=True, help_text=_('Custom HTML for header'))
    footer_html = models.TextField(_('footer HTML'), blank=True, help_text=_('Custom HTML for footer'))
    
    updated_at = models.DateTimeField(_('updated at'), auto_now=True)
    
    class Meta:
        verbose_name = _('site setting')
        verbose_name_plural = _('site settings')
    
    def __str__(self):
        return self.site_name
    
    def save(self, *args, **kwargs):
        # Ensure only one instance exists
        if not self.pk and SiteSettings.objects.exists():
            raise ValueError("SiteSettings already exists. Update the existing instance.")
        return super().save(*args, **kwargs)
