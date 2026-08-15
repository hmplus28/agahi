"""HTTP response caching for anonymous public pages only."""
from functools import wraps

from django.core.cache import cache
from django.utils.cache import patch_cache_control

from .cache_utils import cache_key


def cache_public_page(timeout, version_getter, namespace='public-page'):
    """Cache successful anonymous GET responses without exposing private HTML."""
    def decorator(view):
        @wraps(view)
        def wrapped(request, *args, **kwargs):
            if request.method != 'GET' or request.user.is_authenticated:
                response = view(request, *args, **kwargs)
                patch_cache_control(response, private=True, no_cache=True)
                return response

            version = version_getter()
            key = cache_key(namespace, version, request.get_host(), request.get_full_path())
            response = cache.get(key)
            if response is None:
                response = view(request, *args, **kwargs)
                if response.status_code == 200 and not response.cookies:
                    patch_cache_control(
                        response,
                        public=True,
                        max_age=timeout,
                        s_maxage=timeout,
                        stale_while_revalidate=timeout,
                    )
                    cache.set(key, response, timeout)
            return response
        return wrapped
    return decorator
