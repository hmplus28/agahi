from django.db import models
from django.conf import settings
from django.utils.translation import gettext_lazy as _


class Ticket(models.Model):
    """Support ticket model."""
    STATUS_CHOICES = [
        ('open', _('Open')),
        ('waiting_user', _('Waiting User')),
        ('waiting_support', _('Waiting Support')),
        ('closed', _('Closed')),
    ]
    
    PRIORITY_CHOICES = [
        ('low', _('Low')),
        ('normal', _('Normal')),
        ('high', _('High')),
        ('urgent', _('Urgent')),
    ]
    
    user = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name='tickets',
        verbose_name=_('user'),
    )
    subject = models.CharField(_('subject'), max_length=255)
    status = models.CharField(
        _('status'),
        max_length=20,
        choices=STATUS_CHOICES,
        default='open',
        db_index=True,
    )
    priority = models.CharField(
        _('priority'),
        max_length=20,
        choices=PRIORITY_CHOICES,
        default='normal',
    )
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    updated_at = models.DateTimeField(_('updated at'), auto_now=True)
    closed_at = models.DateTimeField(_('closed at'), null=True, blank=True)
    
    class Meta:
        verbose_name = _('ticket')
        verbose_name_plural = _('tickets')
        ordering = ['-created_at']
    
    def __str__(self):
        return f"Ticket #{self.id} - {self.subject}"
    
    def close(self):
        """Close the ticket."""
        from django.utils import timezone
        self.status = 'closed'
        self.closed_at = timezone.now()
        self.save()


class TicketMessage(models.Model):
    """Ticket message model."""
    ticket = models.ForeignKey(
        Ticket,
        on_delete=models.CASCADE,
        related_name='messages',
        verbose_name=_('ticket'),
    )
    sender = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        verbose_name=_('sender'),
    )
    message = models.TextField(_('message'))
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    
    class Meta:
        verbose_name = _('ticket message')
        verbose_name_plural = _('ticket messages')
        ordering = ['created_at']
    
    def __str__(self):
        return f"Message for Ticket #{self.ticket.id}"
