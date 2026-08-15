from django.core.cache import cache
from django.test import TestCase
from django.urls import reverse

from accounts.models import User
from ads.models import Ad, AdImage, AdStatus
from ads.services.lifecycle import transition
from locations.models import City, Country, Province
from taxonomy.models import Category


class AdvertisementFlowTests(TestCase):
    def setUp(self):
        cache.clear()
        self.user = User.objects.create_user(username='09120000000', mobile='09120000000', password='strong-password-123')
        country = Country.objects.create(name='ایران', slug='iran')
        province = Province.objects.create(country=country, name='تهران', slug='tehran')
        self.city = City.objects.create(province=province, name='تهران', slug='tehran')
        self.category = Category.objects.create(title='خدمات', slug='services')

    def make_ad(self, status=AdStatus.ACTIVE):
        return Ad.objects.create(
            user=self.user,
            title='تعمیرات یخچال',
            description='تعمیرات تخصصی و فوری یخچال در تهران',
            category=self.category,
            country=self.city.province.country,
            province=self.city.province,
            city=self.city,
            mobile_1='09120000000',
            full_name='کاربر آزمایشی',
            status=status,
        )

    def test_ad_normalizes_and_hashes_content(self):
        ad = self.make_ad()
        self.assertEqual(ad.normalized_title, 'تعمیرات یخچال')
        self.assertEqual(len(ad.normalized_title_hash), 64)
        self.assertTrue(ad.slug.startswith(ad.code))

    def test_public_detail_is_rendered_and_counts_once_per_session(self):
        ad = self.make_ad()
        response = self.client.get(reverse('ads:ad_detail', args=[ad.pk]))
        self.assertEqual(response.status_code, 200)
        self.assertContains(response, 'index,follow')
        ad.refresh_from_db()
        self.assertEqual(ad.views_count, 1)
        self.client.get(reverse('ads:ad_detail', args=[ad.pk]))
        ad.refresh_from_db()
        self.assertEqual(ad.views_count, 1)

    def test_soft_delete_uses_lifecycle_and_history(self):
        ad = self.make_ad()
        transition(ad, AdStatus.DELETED, changed_by=self.user, reason='test')
        ad.refresh_from_db()
        self.assertEqual(ad.status, AdStatus.DELETED)
        self.assertIsNotNone(ad.deleted_at)
        self.assertEqual(ad.status_history.count(), 1)

    def test_home_and_listing_load(self):
        self.make_ad()
        self.assertEqual(self.client.get(reverse('core:home')).status_code, 200)
        self.assertEqual(self.client.get(reverse('ads:ad_list')).status_code, 200)

    def test_public_listing_is_cached_and_invalidated_after_ad_change(self):
        ad = self.make_ad()
        url = reverse('ads:ad_list')
        first = self.client.get(url)
        self.assertEqual(first.status_code, 200)
        self.assertIn('public', first['Cache-Control'])
        with self.assertNumQueries(0):
            second = self.client.get(url)
        self.assertContains(second, ad.title)

        fresh = self.make_ad()
        refreshed = self.client.get(url)
        self.assertContains(refreshed, fresh.title)

    def test_listing_supports_gzip_and_normalized_search_fallback(self):
        self.make_ad()
        url = f"{reverse('ads:ad_list')}?q=یخچال"
        response = self.client.get(url, HTTP_ACCEPT_ENCODING='gzip')
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.get('Content-Encoding'), 'gzip')

    def test_first_listing_image_is_prioritized_and_remaining_images_are_lazy(self):
        for index in range(5):
            ad = self.make_ad()
            AdImage.objects.create(
                ad=ad,
                image_thumb=f'ads/thumb-{index}.webp',
                image_display=f'ads/display-{index}.webp',
                thumb_width=480,
                thumb_height=320,
                display_width=1280,
                display_height=853,
                is_primary=True,
            )
        response = self.client.get(reverse('ads:ad_list'))
        self.assertContains(response, 'fetchpriority="high"')
        self.assertContains(response, 'loading="lazy"')
