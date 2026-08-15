from django.core.cache import cache
from django.test import TestCase
from django.urls import reverse
from django.utils import timezone

from accounts.models import User
from ads.models import Ad, AdStatus
from locations.models import City, Country, Province
from taxonomy.models import Category


class SEOPolicyTests(TestCase):
    def setUp(self):
        cache.clear()
        self.user = User.objects.create_user(
            username='09127777777', mobile='09127777777', password='strong-password-123'
        )
        self.country = Country.objects.create(name='ایران', slug='iran')
        self.province = Province.objects.create(country=self.country, name='تهران', slug='tehran-province')
        self.city = City.objects.create(province=self.province, name='تهران', slug='tehran')
        self.category = Category.objects.create(title='صنعتی', slug='industrial')

    def make_ad(self, number, status=AdStatus.ACTIVE, **overrides):
        values = {
            'user': self.user,
            'title': f'دستگاه صنعتی {number}',
            'description': f'توضیحات معتبر برای دستگاه صنعتی شماره {number}',
            'category': self.category,
            'country': self.country,
            'province': self.province,
            'city': self.city,
            'mobile_1': '09127777777',
            'full_name': 'کاربر تست',
            'status': status,
            'published_at': timezone.now(),
        }
        values.update(overrides)
        return Ad.objects.create(**values)

    def test_active_ad_has_canonical_indexable_url_and_legacy_path_redirects(self):
        ad = self.make_ad(1)
        response = self.client.get(ad.get_absolute_url())
        self.assertEqual(response.status_code, 200)
        self.assertContains(response, f'<link rel="canonical" href="http://testserver{ad.get_absolute_url()}">', html=False)
        self.assertContains(response, 'index,follow,max-image-preview:large')
        self.assertContains(response, 'BreadcrumbList')

        legacy = self.client.get(reverse('ads:ad_detail', args=[ad.pk]))
        self.assertEqual(legacy.status_code, 301)
        self.assertEqual(legacy['Location'], ad.get_absolute_url())

        stale = self.client.get(reverse('ad_detail', kwargs={'code': ad.code, 'slug': 'slug-old'}))
        self.assertEqual(stale.status_code, 301)
        self.assertEqual(stale['Location'], ad.get_absolute_url())

    def test_pending_ad_is_not_publicly_indexable(self):
        ad = self.make_ad(2, status=AdStatus.PENDING_APPROVAL)
        response = self.client.get(ad.get_absolute_url())
        self.assertEqual(response.status_code, 302)
        self.assertEqual(response['Location'], reverse('ads:ad_list'))

    def test_search_and_filter_are_noindex_with_canonical_without_query(self):
        self.make_ad(3)
        response = self.client.get(reverse('ads:ad_list'), {'q': 'دستگاه', 'sort': 'price_low'})
        self.assertEqual(response.status_code, 200)
        self.assertContains(response, 'noindex,follow')
        self.assertContains(response, '<link rel="canonical" href="http://testserver/ads/">', html=False)

    def test_category_and_location_landings_follow_policy(self):
        for number in range(4, 7):
            self.make_ad(number)
        category_response = self.client.get(self.category.get_absolute_url())
        self.assertEqual(category_response.status_code, 200)
        self.assertContains(category_response, 'index,follow,max-image-preview:large')
        self.assertContains(category_response, 'BreadcrumbList')

        city_response = self.client.get(reverse('location_detail', kwargs={'slug': self.city.slug}))
        self.assertEqual(city_response.status_code, 200)
        self.assertContains(city_response, 'index,follow,max-image-preview:large')
        self.assertContains(city_response, 'آگهی‌های تهران')

        city_category_response = self.client.get(reverse('location_category_detail', kwargs={
            'location_slug': self.city.slug,
            'category_slug': self.category.slug,
        }))
        self.assertEqual(city_category_response.status_code, 200)
        self.assertContains(city_category_response, 'index,follow,max-image-preview:large')

    def test_sitemap_index_and_segments_include_only_public_canonical_urls(self):
        ads = [self.make_ad(number) for number in range(7, 10)]
        pending = self.make_ad(10, status=AdStatus.PENDING_APPROVAL)
        index_response = self.client.get(reverse('sitemap_xml'))
        self.assertEqual(index_response.status_code, 200)
        self.assertContains(index_response, 'sitemapindex')
        self.assertContains(index_response, '/sitemaps/ads-1.xml')

        ad_segment = self.client.get(reverse('sitemap_ads', kwargs={'page': 1}))
        self.assertEqual(ad_segment.status_code, 200)
        for ad in ads:
            self.assertContains(ad_segment, ad.get_absolute_url())
        self.assertNotContains(ad_segment, pending.get_absolute_url())

        category_segment = self.client.get(reverse('sitemap_categories'))
        self.assertEqual(category_segment.status_code, 200)
        self.assertContains(category_segment, self.category.get_absolute_url())

        location_segment = self.client.get(reverse('sitemap_locations'))
        self.assertEqual(location_segment.status_code, 200)
        self.assertContains(location_segment, reverse('location_detail', kwargs={'slug': self.city.slug}))

    def test_robots_advertises_sitemap_without_blocking_public_filter_path(self):
        response = self.client.get(reverse('robots_txt'))
        self.assertEqual(response.status_code, 200)
        self.assertContains(response, 'Sitemap: http://testserver/sitemap.xml')
        self.assertNotContains(response, 'Disallow: /ads/')
