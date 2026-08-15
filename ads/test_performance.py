from django.core.cache import cache
from django.db import connection
from django.test import TestCase
from django.test.utils import CaptureQueriesContext

from accounts.models import User
from ads.models import Ad, AdStatus
from locations.models import City, Country, Province
from taxonomy.models import Category


class PublicQueryBudgetTests(TestCase):
    def setUp(self):
        self.user = User.objects.create_user(username='09123333333', mobile='09123333333', password='secure-password-123')
        country = Country.objects.create(name='ایران', slug='iran')
        province = Province.objects.create(country=country, name='تهران', slug='tehran')
        self.city = City.objects.create(province=province, name='تهران', slug='tehran')
        self.category = Category.objects.create(title='خدمات', slug='services')
        self.ad = Ad.objects.create(
            user=self.user,
            title='تعمیرات تخصصی آزمایشی',
            description='توضیحات کافی برای سنجش بودجهٔ query صفحهٔ عمومی آگهی.',
            category=self.category,
            country=country,
            province=province,
            city=self.city,
            mobile_1='09123333333',
            full_name='کاربر تست',
            status=AdStatus.ACTIVE,
        )

    def test_listing_cold_cache_query_budget(self):
        cache.clear()
        with CaptureQueriesContext(connection) as queries:
            response = self.client.get('/ads/')
        self.assertEqual(response.status_code, 200)
        self.assertLessEqual(len(queries), 10, f'Listing query budget exceeded: {len(queries)}')

    def test_detail_query_budget(self):
        cache.clear()
        with CaptureQueriesContext(connection) as queries:
            response = self.client.get(self.ad.get_absolute_url())
        self.assertEqual(response.status_code, 200)
        self.assertLessEqual(len(queries), 12, f'Detail query budget exceeded: {len(queries)}')
