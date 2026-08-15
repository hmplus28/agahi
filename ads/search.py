"""Database-aware, Persian-normalized public advertisement search."""
from django.core.cache import cache
from django.db import DatabaseError, connection
from django.db.models import Q

from core.services.persian_normalization import normalize_persian_text

TRIGRAM_CAPABILITY_CACHE_KEY = 'ads:pg-trgm-available:v1'
TRIGRAM_CAPABILITY_TIMEOUT = 3600


def _trigram_available():
    """Return whether this PostgreSQL database actually has pg_trgm enabled."""
    if connection.vendor != 'postgresql':
        return False

    available = cache.get(TRIGRAM_CAPABILITY_CACHE_KEY)
    if available is not None:
        return available

    try:
        with connection.cursor() as cursor:
            cursor.execute("SELECT EXISTS (SELECT 1 FROM pg_extension WHERE extname = 'pg_trgm')")
            available = bool(cursor.fetchone()[0])
    except DatabaseError:
        available = False
    cache.set(TRIGRAM_CAPABILITY_CACHE_KEY, available, TRIGRAM_CAPABILITY_TIMEOUT)
    return available


def _portable_search_filter(query):
    return (
        Q(normalized_title__icontains=query)
        | Q(normalized_description__icontains=query)
        | Q(business_name__icontains=query)
        | Q(category__title__icontains=query)
        | Q(city__name__icontains=query)
    )


def search_ads(queryset, query):
    """Search all required public fields with an indexed trigram path when available.

    The portable fallback keeps the site functional on SQLite and PostgreSQL
    hosts where the optional pg_trgm extension cannot be enabled.
    """
    query = normalize_persian_text((query or '').strip())
    if not query:
        return queryset, None

    if len(query) >= 3 and _trigram_available():
        from django.contrib.postgres.search import TrigramSimilarity

        similarity = (
            TrigramSimilarity('normalized_title', query) * 0.55
            + TrigramSimilarity('normalized_description', query) * 0.20
            + TrigramSimilarity('business_name', query) * 0.15
            + TrigramSimilarity('category__title', query) * 0.05
            + TrigramSimilarity('city__name', query) * 0.05
        )
        return (
            queryset.annotate(search_rank=similarity)
            .filter(search_rank__gte=0.12)
            .order_by('-search_rank'),
            '-search_rank',
        )

    return queryset.filter(_portable_search_filter(query)), None
