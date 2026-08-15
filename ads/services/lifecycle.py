"""Controlled advertisement state transitions."""
from django.core.exceptions import ValidationError
from django.db import transaction
from django.utils import timezone

from ads.models import AdStatus, AdStatusHistory

ALLOWED_TRANSITIONS = {
    AdStatus.DRAFT: {AdStatus.PENDING_APPROVAL, AdStatus.DELETED},
    AdStatus.PENDING_APPROVAL: {AdStatus.ACTIVE, AdStatus.NEEDS_PERMIT, AdStatus.INACTIVE, AdStatus.DELETED},
    AdStatus.ACTIVE: {AdStatus.PENDING_APPROVAL, AdStatus.INACTIVE, AdStatus.EXPIRED, AdStatus.DELETED},
    AdStatus.NEEDS_PERMIT: {AdStatus.PENDING_APPROVAL, AdStatus.ACTIVE, AdStatus.INACTIVE, AdStatus.DELETED},
    AdStatus.INACTIVE: {AdStatus.PENDING_APPROVAL, AdStatus.ACTIVE, AdStatus.DELETED},
    AdStatus.EXPIRED: {AdStatus.PENDING_APPROVAL, AdStatus.ACTIVE, AdStatus.DELETED},
    AdStatus.DELETED: {AdStatus.INACTIVE},
}


def transition(ad, target_status, *, changed_by=None, reason='', force=False):
    """Move an advertisement between explicit business states atomically."""
    current_status = ad.status
    if target_status == current_status:
        return ad
    if not force and target_status not in ALLOWED_TRANSITIONS.get(current_status, set()):
        raise ValidationError('تغییر وضعیت آگهی مطابق گردش‌کار مجاز نیست.')

    with transaction.atomic():
        ad.status = target_status
        now = timezone.now()
        if target_status == AdStatus.ACTIVE and not ad.published_at:
            ad.published_at = now
        if target_status == AdStatus.DELETED:
            ad.deleted_at = now
        elif current_status == AdStatus.DELETED:
            ad.deleted_at = None
        ad.save(update_fields=['status', 'published_at', 'deleted_at', 'updated_at'])
        AdStatusHistory.objects.create(
            ad=ad,
            from_status=current_status,
            to_status=target_status,
            changed_by=changed_by,
            reason=reason,
        )
    return ad
