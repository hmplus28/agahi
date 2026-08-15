"""Account model signals."""
from django.db.models.signals import post_save
from django.dispatch import receiver

from .models import Profile, User


@receiver(post_save, sender=User)
def ensure_profile(sender, instance, created, **kwargs):
    """Every user has a profile for dashboard updates without nullable assumptions."""
    if created:
        Profile.objects.get_or_create(user=instance)
