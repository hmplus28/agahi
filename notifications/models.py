from django.db import models
from django.conf import settings
from django.utils.translation import gettext_lazy as _


class SMSLog(models.Model):
    """SMS log model for tracking sent messages."""
    TYPE_CHOICES = [
        ('verification', _('Verification')),
        ('ad_expiry', _('Ad Expiry')),
        ('payment', _('Payment')),
        ('ticket', _('Ticket')),
        ('admin', _('Admin')),
        ('other', _('Other')),
    ]
    
    STATUS_CHOICES = [
        ('sent', _('Sent')),
        ('failed', _('Failed')),
        ('pending', _('Pending')),
    ]
    
    user = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name='sms_logs',
        verbose_name=_('user'),
    )
    ad = models.ForeignKey(
        'ads.Ad',
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name='sms_logs',
        verbose_name=_('ad'),
    )
    mobile = models.CharField(_('mobile'), max_length=11)
    type = models.CharField(
        _('type'),
        max_length=20,
        choices=TYPE_CHOICES,
        default='other',
    )
    provider_id = models.CharField(_('provider ID'), max_length=100, blank=True)
    status = models.CharField(
        _('status'),
        max_length=20,
        choices=STATUS_CHOICES,
        default='pending',
    )
    sent_at = models.DateTimeField(_('sent at'), null=True, blank=True)
    response = models.TextField(_('response'), blank=True)
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    
    class Meta:
        verbose_name = _('SMS log')
        verbose_name_plural = _('SMS logs')
        ordering = ['-created_at']
    
    def __str__(self):
        return f"SMS to {self.mobile} - {self.status}"
