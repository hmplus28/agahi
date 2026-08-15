"""Database-aware public advertisement search."""
from django.db import connection
from django.db.models import Q


def search_ads(queryset, query):
    """Use indexed PostgreSQL trigrams where available; retain a portable fallback."""
    query = (query or '').strip()
    if not query:
        return queryset, None

    if connection.vendor == 'postgresql' and len(query) >= 3:
        from django.contrib.postgres.search import TrigramSimilarity

        similarity = (
            TrigramSimilarity('normalized_title', query)
            + TrigramSimilarity('normalized_description', query)
        )
        return (
            queryset.annotate(search_rank=similarity)
            .filter(search_rank__gte=0.12)
            .order_by('-search_rank'),
            '-search_rank',
        )

    return queryset.filter(
        Q(normalized_title__icontains=query) | Q(normalized_description__icontains=query)
    ), None
