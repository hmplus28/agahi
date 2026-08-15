"""Duplicate advertisement detection using the current domain schema."""
import hashlib
from datetime import timedelta
from typing import Optional

from django.db.models import Q
from django.utils import timezone

from ads.models import Ad, AdStatus
from core.services.persian_normalization import normalize_for_comparison


class DuplicateDetectionService:
    """Find likely duplicate submissions without blocking administrator review."""

    duplicate_time_window = timedelta(days=7)

    @staticmethod
    def _digest(value):
        return hashlib.sha256(value.encode('utf-8')).hexdigest()

    def find_duplicates(
        self,
        *,
        title: str,
        description: str,
        mobile: str,
        category_id: Optional[int],
        city_id: Optional[int] = None,
        exclude_ad_id: Optional[int] = None,
    ):
        normalized_title = normalize_for_comparison(title)
        normalized_description = normalize_for_comparison(description)
        cutoff = timezone.now() - self.duplicate_time_window
        query = Q(
            created_at__gte=cutoff,
            status__in=[AdStatus.PENDING_APPROVAL, AdStatus.ACTIVE],
            deleted_at__isnull=True,
        )
        if category_id:
            query &= Q(category_id=category_id)
        if city_id:
            query &= Q(city_id=city_id)
        if exclude_ad_id:
            query &= ~Q(pk=exclude_ad_id)

        fingerprints = Q(normalized_title_hash=self._digest(normalized_title))
        if normalized_description:
            fingerprints |= Q(normalized_description_hash=self._digest(normalized_description))
        if mobile:
            fingerprints |= Q(mobile_1=mobile) | Q(mobile_2=mobile)
        return list(Ad.objects.filter(query & fingerprints).order_by('-created_at')[:10])

    def is_duplicate(self, **kwargs):
        duplicates = self.find_duplicates(**kwargs)
        return bool(duplicates), duplicates


duplicate_detector = DuplicateDetectionService()


def detect_duplicate_ads(**kwargs):
    """Convenience wrapper used by create and edit workflows."""
    return duplicate_detector.is_duplicate(**kwargs)
