from django.contrib.auth.models import AbstractUser
from django.db import models
from django.utils.translation import gettext_lazy as _


class User(AbstractUser):
    """
    Custom User model with mobile-based authentication.
    Email is optional, mobile is the primary identifier.
    """
    mobile = models.CharField(
        _('mobile'),
        max_length=11,
        unique=True,
        help_text=_('Mobile number without leading zero.')
    )
    email = models.EmailField(
        _('email address'),
        blank=True,
        null=True,
    )
    
    class Meta:
        verbose_name = _('user')
        verbose_name_plural = _('users')
        ordering = ['-date_joined']
    
    def __str__(self):
        return self.mobile
    
    @property
    def full_name(self):
        """Return full name of user."""
        if self.first_name and self.last_name:
            return f"{self.first_name} {self.last_name}"
        return self.first_name or self.mobile


class Profile(models.Model):
    """
    User profile with additional information.
    """
    user = models.OneToOneField(
        User,
        on_delete=models.CASCADE,
        related_name='profile',
        verbose_name=_('user'),
    )
    business_name = models.CharField(
        _('business name'),
        max_length=255,
        blank=True,
    )
    address = models.TextField(
        _('address'),
        blank=True,
    )
    province = models.ForeignKey(
        'locations.Province',
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        verbose_name=_('province'),
    )
    city = models.ForeignKey(
        'locations.City',
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        verbose_name=_('city'),
    )
    postal_code = models.CharField(
        _('postal code'),
        max_length=10,
        blank=True,
    )
    avatar = models.ImageField(
        _('avatar'),
        upload_to='avatars/',
        blank=True,
    )
    created_at = models.DateTimeField(
        _('created at'),
        auto_now_add=True,
    )
    updated_at = models.DateTimeField(
        _('updated at'),
        auto_now=True,
    )
    
    class Meta:
        verbose_name = _('profile')
        verbose_name_plural = _('profiles')
    
    def __str__(self):
        return f"Profile of {self.user.mobile}"
