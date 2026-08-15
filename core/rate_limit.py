"""Small cache-backed fixed-window rate limiting for sensitive request endpoints."""
from functools import wraps
from hashlib import sha256
from time import time

from django.core.cache import cache
from django.http import HttpResponse


def _client_identity(request) -> str:
    forwarded = request.META.get('HTTP_X_FORWARDED_FOR', '')
    ip_address = forwarded.split(',')[0].strip() if forwarded else request.META.get('REMOTE_ADDR', '')
    user_part = str(request.user.pk) if request.user.is_authenticated else 'anonymous'
    return sha256(f'{user_part}:{ip_address}'.encode('utf-8')).hexdigest()[:24]


def rate_limit(namespace: str, *, limit: int, period: int, methods=('POST',)):
    """Limit a request identity to a bounded number of operations per time window.

    The limiter deliberately fails open if an optional cache backend is unavailable;
    CSRF and Django authentication checks remain independent safeguards.
    """
    def decorator(view_func):
        @wraps(view_func)
        def wrapped(request, *args, **kwargs):
            if request.method not in methods:
                return view_func(request, *args, **kwargs)
            window = int(time() // period)
            key = f'rate-limit:{namespace}:{window}:{_client_identity(request)}'
            try:
                if cache.add(key, 1, timeout=period):
                    return view_func(request, *args, **kwargs)
                current = cache.get(key, 0)
                if current >= limit:
                    response = HttpResponse('تعداد درخواست‌ها بیش از حد مجاز است. چند دقیقه دیگر دوباره تلاش کنید.', status=429)
                    response['Retry-After'] = str(period)
                    return response
                cache.incr(key)
            except Exception:
                # A cache failure must not make the public site unavailable.
                pass
            return view_func(request, *args, **kwargs)
        return wrapped
    return decorator
