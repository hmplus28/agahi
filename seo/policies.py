"""Central, conservative SEO indexability and canonical URL policy."""
from urllib.parse import urlencode

from django.conf import settings
from django.utils import timezone

from ads.models import AdStatus


FILTER_QUERY_PARAMETERS = frozenset({
    'q', 'category', 'city', 'min_price', 'max_price', 'sort', 'urgent', 'page_size', 'view',
})
PAGINATION_QUERY_PARAMETERS = frozenset({'page'})
PRIVATE_PATH_PREFIXES = (
    '/admin/', '/accounts/', '/dashboard/', '/billing/', '/support/', '/notifications/', '/moderation/',
)


def is_indexable_ad(ad) -> bool:
    """Only a live, approved ad with remaining validity can be indexed."""
    return bool(
        ad.status == AdStatus.ACTIVE
        and not ad.deleted_at
        and (ad.expires_at is None or ad.expires_at > timezone.now())
    )


def is_indexable_category(category, ad_count: int) -> bool:
    """Avoid indexing inactive or thin category pages."""
    return bool(category and category.is_active and ad_count > 0)


def is_indexable_location(city, ad_count: int) -> bool:
    """A location landing becomes indexable only after it contains useful inventory."""
    threshold = max(1, int(getattr(settings, 'SEO_LANDING_MIN_ADS', 3)))
    return bool(city and city.is_active and ad_count >= threshold)


def is_indexable_category_location(category, city, ad_count: int) -> bool:
    """Keep city/category landing pages policy-based instead of indexing every combination."""
    return bool(category and category.is_active and is_indexable_location(city, ad_count))


def request_has_arbitrary_filters(request) -> bool:
    """Return whether a request contains search or facet parameters that must be noindexed."""
    return bool(set(request.GET) & FILTER_QUERY_PARAMETERS)


def canonical_query_string(request, *, allow_pagination=False) -> str:
    """Keep only a valid pagination parameter when a public collection supports it."""
    if not allow_pagination:
        return ''
    page = request.GET.get('page', '').strip()
    if page.isdigit() and int(page) > 1:
        return urlencode({'page': page})
    return ''


def canonical_url(request, *, path=None, allow_pagination=False) -> str:
    """Build an absolute canonical URL without search or arbitrary facet parameters."""
    path = path or request.path
    query = canonical_query_string(request, allow_pagination=allow_pagination)
    absolute = request.build_absolute_uri(path)
    return f'{absolute}?{query}' if query else absolute


def robots_directive(*, indexable: bool, request=None) -> str:
    """Return the one authoritative robots directive used by templates and sitemaps."""
    if request and (request.path.startswith(PRIVATE_PATH_PREFIXES) or request_has_arbitrary_filters(request)):
        return 'noindex,follow'
    return 'index,follow,max-image-preview:large' if indexable else 'noindex,follow'


def page_seo_context(request, *, indexable: bool, path=None, allow_pagination=False) -> dict:
    """Template context shared by public views; keeps canonical and robots aligned."""
    return {
        'canonical_url': canonical_url(request, path=path, allow_pagination=allow_pagination),
        'seo_robots': robots_directive(indexable=indexable, request=request),
    }
