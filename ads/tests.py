from io import BytesIO

from django.core.cache import cache
from django.core.files.uploadedfile import SimpleUploadedFile
from django.test import TestCase
from django.urls import reverse
from PIL import Image

from accounts.models import User
from ads.forms import AdForm
from ads.models import Ad, AdImage, AdReport, AdStatus, ForbiddenWord
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

    def make_ad(self, status=AdStatus.ACTIVE, **overrides):
        values = {
            'user': self.user,
            'title': 'تعمیرات یخچال',
            'description': 'تعمیرات تخصصی و فوری یخچال در تهران',
            'category': self.category,
            'country': self.city.province.country,
            'province': self.city.province,
            'city': self.city,
            'mobile_1': '09120000000',
            'full_name': 'کاربر آزمایشی',
            'status': status,
        }
        values.update(overrides)
        return Ad.objects.create(**values)

    def test_ad_normalizes_and_hashes_content(self):
        ad = self.make_ad()
        self.assertEqual(ad.normalized_title, 'تعمیرات یخچال')
        self.assertEqual(len(ad.normalized_title_hash), 64)
        self.assertTrue(ad.slug.startswith(ad.code))

    def test_public_detail_is_rendered_and_counts_once_per_session(self):
        ad = self.make_ad()
        response = self.client.get(ad.get_absolute_url())
        self.assertEqual(response.status_code, 200)
        self.assertContains(response, 'index,follow')
        ad.refresh_from_db()
        self.assertEqual(ad.views_count, 1)
        self.client.get(ad.get_absolute_url())
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
        decoded = response.content
        if response.get('Content-Encoding') == 'gzip':
            import gzip
            decoded = gzip.decompress(decoded)
        self.assertIn(b'noindex,follow', decoded)

    def test_search_covers_title_description_business_category_and_city(self):
        default_ad = self.make_ad()
        self.make_ad(
            title='عنوان منحصربه‌فرد',
            description='شرح متفاوت',
            business_name='کارگاه آریا',
        )
        for query in ('عنوان منحصربه‌فرد', 'شرح متفاوت', 'آریا', self.category.title, self.city.name):
            response = self.client.get(reverse('ads:ad_list'), {'q': query})
            self.assertEqual(response.status_code, 200)
            self.assertContains(response, 'عنوان منحصربه‌فرد')

        normalized_response = self.client.get(reverse('ads:ad_list'), {'q': 'يخچال'})
        self.assertEqual(normalized_response.status_code, 200)
        self.assertContains(normalized_response, default_ad.title)

    def test_search_query_is_escaped_in_html(self):
        response = self.client.get(f"{reverse('ads:ad_list')}?q=<script>alert(1)</script>")
        self.assertEqual(response.status_code, 200)
        self.assertNotContains(response, '<script>alert(1)</script>', html=True)
        self.assertContains(response, '&lt;script&gt;alert(1)&lt;/script&gt;', html=False)

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

    def _form_payload(self, **overrides):
        data = {
            'category': self.category.pk,
            'title': 'تعمیرات یخچال در محل',
            'description': 'تعمیرات تخصصی برای همه مدل‌های یخچال در تهران',
            'keywords': 'تعمیرات, یخچال',
            'price': '',
            'country': self.city.province.country_id,
            'province': self.city.province_id,
            'city': self.city.pk,
            'address': '',
            'mobile_1': '09120000000',
            'show_mobile_1': 'on',
            'mobile_2': '',
            'phone_1': '',
            'phone_2': '',
            'email': '',
            'full_name': 'کاربر آزمایشی',
            'business_name': '',
        }
        data.update(overrides)
        return data

    def test_forbidden_word_is_rejected_by_ad_form(self):
        ForbiddenWord.objects.create(word='غیرمجاز', normalized_word='غیرمجاز')
        form = AdForm(data=self._form_payload(description='این متن شامل عبارت غیرمجاز است.'))
        self.assertFalse(form.is_valid())
        self.assertIn('عبارت غیرمجاز', form.non_field_errors().as_text())

    def test_duplicate_is_rejected_when_creating_ad(self):
        self.make_ad()
        form = AdForm(data=self._form_payload(title='تعمیرات یخچال', description='تعمیرات تخصصی و فوری یخچال در تهران'))
        self.assertFalse(form.is_valid())
        self.assertIn('آگهی مشابهی', form.non_field_errors().as_text())

    def test_expired_ad_hides_contact_for_public_visitors(self):
        ad = self.make_ad(status=AdStatus.EXPIRED, mobile_1='09121112222')
        response = self.client.get(ad.get_absolute_url())
        self.assertEqual(response.status_code, 200)
        self.assertContains(response, 'این آگهی منقضی شده است')
        self.assertNotContains(response, '09121112222')
        self.assertContains(response, 'noindex,follow')

    def test_owner_can_submit_optimized_permit_for_review(self):
        ad = self.make_ad(status=AdStatus.NEEDS_PERMIT)
        image_stream = BytesIO()
        Image.new('RGB', (24, 18), 'white').save(image_stream, format='PNG')
        self.client.force_login(self.user)
        response = self.client.post(
            reverse('ads:ad_permit_upload', args=[ad.pk]),
            data={
                'permit_number': 'PERMIT-1',
                'issuer': 'مرجع آزمایشی',
                'issued_at': '2026-01-01',
                'image': SimpleUploadedFile('permit.png', image_stream.getvalue(), content_type='image/png'),
            },
        )
        self.assertRedirects(response, reverse('dashboard:my_ads'))
        ad.refresh_from_db()
        self.assertEqual(ad.permit.status, 'pending')
        self.assertTrue(ad.permit.image.name.endswith('.webp'))

    def test_public_report_is_saved_and_rate_limited(self):
        cache.clear()
        ad = self.make_ad()
        url = reverse('ads:ad_report_create', args=[ad.pk])
        reason = AdReport.REASON_CHOICES[0][0]
        for _ in range(5):
            response = self.client.post(url, {'reason': reason, 'description': 'متن گزارش آزمایشی'})
            self.assertEqual(response.status_code, 302)
        throttled = self.client.post(url, {'reason': reason, 'description': 'گزارش اضافی'})
        self.assertEqual(throttled.status_code, 429)
        self.assertEqual(AdReport.objects.filter(ad=ad).count(), 5)
