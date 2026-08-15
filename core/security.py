"""Security response headers appropriate for the server-rendered public application."""
from django.conf import settings


class PublicSecurityHeadersMiddleware:
    """Set defense-in-depth headers without requiring JavaScript nonces.

    Django Admin is intentionally exempt from the public CSP because it uses inline
    bootstrap snippets; it remains protected by Django's built-in CSRF and security
    middleware and can receive a nonce-based policy in a future dedicated admin pass.
    """

    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):
        response = self.get_response(request)
        response.setdefault('Referrer-Policy', 'strict-origin-when-cross-origin')
        response.setdefault('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
        response.setdefault('Cross-Origin-Opener-Policy', 'same-origin')
        if not request.path.startswith('/admin/'):
            response.setdefault(
                'Content-Security-Policy',
                "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; "
                "object-src 'none'; img-src 'self' data:; font-src 'self'; style-src 'self'; script-src 'self'",
            )
        return response
