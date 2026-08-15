from django.test import TestCase, override_settings
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
        self.assertJSONEqual(response.content, {'status': 'ok', 'offline_mode': False})
        self.assertEqual(response['X-Robots-Tag'], 'noindex, noarchive')
        self.assertEqual(response['Cache-Control'], 'no-store')

    def test_service_worker_is_root_scoped_and_noncacheable(self):
        response = self.client.get(reverse('core:service_worker'))
        self.assertEqual(response.status_code, 200)
        self.assertTrue(response['Content-Type'].startswith('application/javascript'))
        self.assertEqual(response['Service-Worker-Allowed'], '/')
        self.assertEqual(response['Cache-Control'], 'no-cache, no-store, must-revalidate')
        self.assertEqual(response['X-Robots-Tag'], 'noindex, noarchive')
        self.assertContains(response, "const CACHE_NAME = 'agahi-offline-v1'")
        self.assertContains(response, "'/offline/'")
        self.assertContains(response, "'/dashboard/'")
        self.assertContains(response, "'/media/permits/'")
        self.assertNotIn('https://', response.content.decode('utf-8'))

    def test_offline_fallback_and_self_hosted_bootstrap_render(self):
        fallback = self.client.get(reverse('core:offline_page'))
        self.assertEqual(fallback.status_code, 200)
        self.assertContains(fallback, 'اتصال در دسترس نیست')
        self.assertContains(fallback, 'noindex,nofollow')
        home = self.client.get(reverse('core:home'))
        self.assertContains(home, '/static/js/offline.js')
        self.assertContains(home, '/static/css/style.css')

    @override_settings(OFFLINE_MODE=True)
    def test_healthcheck_reports_active_offline_mode(self):
        response = self.client.get(reverse('core:healthcheck'))
        self.assertEqual(response.status_code, 200)
        self.assertJSONEqual(response.content, {'status': 'ok', 'offline_mode': True})
