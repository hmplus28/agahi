from django.test import TestCase
from django.urls import reverse


class PublicSecurityHeaderTests(TestCase):
    def test_public_response_has_defense_in_depth_headers(self):
        response = self.client.get(reverse('core:home'))
        self.assertEqual(response.status_code, 200)
        self.assertIn("default-src 'self'", response['Content-Security-Policy'])
        self.assertEqual(response['Referrer-Policy'], 'strict-origin-when-cross-origin')
        self.assertEqual(response['Permissions-Policy'], 'camera=(), microphone=(), geolocation=()')
        self.assertEqual(response['Cross-Origin-Opener-Policy'], 'same-origin')

    def test_healthcheck_is_database_aware_and_not_indexable(self):
        response = self.client.get(reverse('core:healthcheck'))
        self.assertEqual(response.status_code, 200)
        self.assertJSONEqual(response.content, {'status': 'ok'})
        self.assertEqual(response['X-Robots-Tag'], 'noindex, noarchive')
        self.assertEqual(response['Cache-Control'], 'no-store')
