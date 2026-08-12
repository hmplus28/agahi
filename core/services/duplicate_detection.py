"""
Duplicate ad detection service.
Detects potential duplicate ads based on title, description, phone, and images.
"""
from typing import List, Optional, Tuple
from django.db.models import Q
from django.utils import timezone
from datetime import timedelta

from ads.models import Ad
from core.services.persian_normalization import normalize_for_comparison


class DuplicateDetectionService:
    """Service for detecting duplicate ads."""
    
    # Time window for checking duplicates (7 days)
    DUPLICATE_TIME_WINDOW = timedelta(days=7)
    
    # Similarity thresholds
    TITLE_SIMILARITY_THRESHOLD = 0.8
    DESCRIPTION_SIMILARITY_THRESHOLD = 0.7
    
    def __init__(self):
        pass
    
    def find_duplicates(
        self,
        title: str,
        description: str,
        phone: str,
        category_id: int,
        location_city_id: Optional[int] = None,
        exclude_ad_id: Optional[int] = None
    ) -> List[Ad]:
        """
        Find potential duplicate ads.
        
        Args:
            title: Ad title
            description: Ad description
            phone: Contact phone number
            category_id: Category ID
            location_city_id: City ID (optional)
            exclude_ad_id: Ad ID to exclude from search (for updates)
            
        Returns:
            List of potential duplicate ads
        """
        # Normalize inputs
        normalized_title = normalize_for_comparison(title)
        normalized_description = normalize_for_comparison(description)
        normalized_phone = self._normalize_phone(phone)
        
        # Base query - active ads within time window
        cutoff_date = timezone.now() - self.DUPLICATE_TIME_WINDOW
        query = Q(
            created_at__gte=cutoff_date,
            status__in=['active', 'pending']
        )
        
        # Exclude current ad if updating
        if exclude_ad_id:
            query &= ~Q(id=exclude_ad_id)
        
        # Filter by category
        query &= Q(category_id=category_id)
        
        # Filter by location if provided
        if location_city_id:
            query &= Q(location_city_id=location_city_id)
        
        candidates = Ad.objects.filter(query)
        
        duplicates = []
        for ad in candidates:
            score = self._calculate_similarity_score(
                ad,
                normalized_title,
                normalized_description,
                normalized_phone
            )
            
            if score >= self.TITLE_SIMILARITY_THRESHOLD:
                duplicates.append(ad)
        
        return duplicates
    
    def _calculate_similarity_score(
        self,
        ad: Ad,
        normalized_title: str,
        normalized_description: str,
        normalized_phone: str
    ) -> float:
        """
        Calculate similarity score between new ad and existing ad.
        
        Returns:
            Score between 0.0 and 1.0
        """
        scores = []
        
        # Title similarity
        ad_title_normalized = normalize_for_comparison(ad.title)
        title_score = self._string_similarity(normalized_title, ad_title_normalized)
        scores.append(title_score * 0.4)  # 40% weight
        
        # Description similarity
        ad_desc_normalized = normalize_for_comparison(ad.description or '')
        desc_score = self._string_similarity(normalized_description, ad_desc_normalized)
        scores.append(desc_score * 0.3)  # 30% weight
        
        # Phone similarity
        if normalized_phone and ad.phone:
            ad_phone_normalized = self._normalize_phone(ad.phone)
            phone_score = 1.0 if normalized_phone == ad_phone_normalized else 0.0
            scores.append(phone_score * 0.3)  # 30% weight
        else:
            scores.append(0.0)
        
        return sum(scores)
    
    def _string_similarity(self, s1: str, s2: str) -> float:
        """
        Calculate string similarity using simple ratio.
        Uses Levenshtein-like approach for Persian text.
        """
        if not s1 and not s2:
            return 1.0
        if not s1 or not s2:
            return 0.0
        
        # Simple ratio: length of common subsequences / max length
        len1, len2 = len(s1), len(s2)
        if len1 == 0 or len2 == 0:
            return 0.0
        
        # Check for substring match
        if s1 in s2 or s2 in s1:
            return min(len1, len2) / max(len1, len2)
        
        # Count common characters
        common = sum(1 for c in s1 if c in s2)
        return common / max(len1, len2)
    
    def _normalize_phone(self, phone: str) -> str:
        """Normalize phone number for comparison."""
        if not phone:
            return ''
        
        # Remove all non-digit characters
        digits = ''.join(c for c in phone if c.isdigit())
        
        # Remove leading zeros
        digits = digits.lstrip('0')
        
        # Add country code if missing (Iran: +98)
        if len(digits) < 10:
            return digits
        elif len(digits) == 10:
            return '98' + digits
        elif len(digits) == 11 and digits.startswith('9'):
            return '98' + digits
        elif len(digits) > 12:
            return digits[-12:]
        
        return digits
    
    def is_duplicate(self, *args, **kwargs) -> Tuple[bool, List[Ad]]:
        """
        Check if ad is a duplicate.
        
        Returns:
            Tuple of (is_duplicate: bool, duplicates: List[Ad])
        """
        duplicates = self.find_duplicates(*args, **kwargs)
        return len(duplicates) > 0, duplicates


# Singleton instance
duplicate_detector = DuplicateDetectionService()


def detect_duplicate_ads(
    title: str,
    description: str,
    phone: str,
    category_id: int,
    location_city_id: Optional[int] = None,
    exclude_ad_id: Optional[int] = None
) -> Tuple[bool, List[Ad]]:
    """Convenience function for duplicate detection."""
    return duplicate_detector.is_duplicate(
        title=title,
        description=description,
        phone=phone,
        category_id=category_id,
        location_city_id=location_city_id,
        exclude_ad_id=exclude_ad_id
    )
