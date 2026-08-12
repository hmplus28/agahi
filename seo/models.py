from django.db import models
from django.utils.translation import gettext_lazy as _


class SEORedirect(models.Model):
    """
    Model for managing SEO redirects (301 redirects).
    Used when slugs change but we want to preserve SEO value.
    """
    old_path = models.CharField(_('old path'), max_length=500, db_index=True)
    new_path = models.CharField(_('new path'), max_length=500)
    status_code = models.PositiveIntegerField(
        _('status code'),
        default=301,
        help_text=_('HTTP status code, typically 301 for permanent redirect')
    )
    is_active = models.BooleanField(_('is active'), default=True)
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    
    class Meta:
        verbose_name = _('SEO redirect')
        verbose_name_plural = _('SEO redirects')
        ordering = ['-created_at']
        unique_together = [['old_path', 'is_active']]
    
    def __str__(self):
        return f"{self.old_path} → {self.new_path}"


class SitemapURL(models.Model):
    """
    Model for tracking sitemap URLs (optional, for large sites).
    Can be used to cache sitemap generation.
    """
    url = models.URLField(_('URL'), max_length=2048)
    changefreq = models.CharField(
        _('change frequency'),
        max_length=20,
        choices=[
            ('always', 'Always'),
            ('hourly', 'Hourly'),
            ('daily', 'Daily'),
            ('weekly', 'Weekly'),
            ('monthly', 'Monthly'),
            ('yearly', 'Yearly'),
            ('never', 'Never'),
        ],
        default='weekly',
    )
    priority = models.DecimalField(
        _('priority'),
        max_digits=2,
        decimal_places=1,
        default=0.5,
        help_text=_('Priority from 0.0 to 1.0')
    )
    last_modified = models.DateTimeField(_('last modified'), null=True, blank=True)
    is_indexable = models.BooleanField(_('is indexable'), default=True)
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    
    class Meta:
        verbose_name = _('sitemap URL')
        verbose_name_plural = _('sitemap URLs')
        indexes = [
            models.Index(fields=['is_indexable', 'last_modified']),
        ]
    
    def __str__(self):
        return self.url
