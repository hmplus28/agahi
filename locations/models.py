from django.db import models
from django.utils.translation import gettext_lazy as _


class Country(models.Model):
    """Country model for location hierarchy."""
    name = models.CharField(_('name'), max_length=100)
    slug = models.SlugField(_('slug'), unique=True)
    is_active = models.BooleanField(_('is active'), default=True)
    sort_order = models.PositiveIntegerField(_('sort order'), default=0)
    
    class Meta:
        verbose_name = _('country')
        verbose_name_plural = _('countries')
        ordering = ['sort_order', 'name']
    
    def __str__(self):
        return self.name


class Province(models.Model):
    """Province model for location hierarchy."""
    country = models.ForeignKey(
        Country,
        on_delete=models.PROTECT,
        related_name='provinces',
        verbose_name=_('country'),
    )
    name = models.CharField(_('name'), max_length=100)
    slug = models.SlugField(_('slug'), unique=True)
    is_active = models.BooleanField(_('is active'), default=True)
    sort_order = models.PositiveIntegerField(_('sort order'), default=0)
    
    class Meta:
        verbose_name = _('province')
        verbose_name_plural = _('provinces')
        ordering = ['sort_order', 'name']
        unique_together = [['country', 'slug']]
    
    def __str__(self):
        return self.name


class City(models.Model):
    """City model for location hierarchy."""
    province = models.ForeignKey(
        Province,
        on_delete=models.PROTECT,
        related_name='cities',
        verbose_name=_('province'),
    )
    name = models.CharField(_('name'), max_length=100)
    slug = models.SlugField(_('slug'))
    is_active = models.BooleanField(_('is active'), default=True)
    sort_order = models.PositiveIntegerField(_('sort order'), default=0)
    
    class Meta:
        verbose_name = _('city')
        verbose_name_plural = _('cities')
        ordering = ['sort_order', 'name']
        unique_together = [['province', 'slug']]
    
    def __str__(self):
        return self.name
