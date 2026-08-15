from django.core.exceptions import ValidationError
from django.test import TestCase, override_settings
from django.urls import reverse

from accounts.models import User
from ads.models import Ad, AdStatus
from billing.models import Payment, Tariff
from billing.services.orders import create_checkout
from locations.models import City, Country, Province
from taxonomy.models import Category


class BillingTests(TestCase):
    def setUp(self):
        self.user = User.objects.create_user(username='09124444444', mobile='09124444444', password='secure-password-123')
        country = Country.objects.create(name='ایران', slug='iran')
        province = Province.objects.create(country=country, name='تهران', slug='tehran')
        city = City.objects.create(province=province, name='تهران', slug='tehran')
        category = Category.objects.create(title='خدمات', slug='services')
        self.ad = Ad.objects.create(user=self.user, title='خدمت آزمایشی', description='توضیحات کافی برای تست', category=category, country=country, province=province, city=city, mobile_1='09124444444', full_name='کاربر تست', status=AdStatus.EXPIRED)

    def test_free_renewal_creates_paid_records_and_renews_ad(self):
        tariff = Tariff.objects.create(code='free-renewal', title='تمدید رایگان', price=0, service_type='annual_renewal', duration_days=30)
        order, invoice, payment = create_checkout(user=self.user, tariff=tariff, ad=self.ad)
        payment.refresh_from_db()
        invoice.refresh_from_db()
        self.ad.refresh_from_db()
        self.assertEqual(payment.status, 'successful')
        self.assertEqual(invoice.status, 'paid')
        self.assertEqual(self.ad.status, AdStatus.ACTIVE)
        self.assertIsNotNone(self.ad.expires_at)
        self.assertEqual(self.ad.status_history.latest('created_at').to_status, AdStatus.ACTIVE)

    def test_expired_ad_rejects_non_renewal_service(self):
        tariff = Tariff.objects.create(
            code='featured-expired',
            title='ویژه',
            price=0,
            service_type='featured',
            duration_days=30,
        )
        with self.assertRaisesMessage(ValidationError, 'فقط خدمت تمدید'):
            create_checkout(user=self.user, tariff=tariff, ad=self.ad)

    @override_settings(DEFAULT_PAYMENT_GATEWAY='zarinpal', ZARINPAL_MERCHANT_ID='')
    def test_paid_checkout_without_gateway_credentials_enters_manual_review(self):
        tariff = Tariff.objects.create(
            code='paid-renewal',
            title='تمدید پولی',
            price=100000,
            service_type='annual_renewal',
            duration_days=30,
        )
        self.client.force_login(self.user)
        response = self.client.post(reverse('billing:checkout', args=[tariff.pk, self.ad.pk]))
        self.assertRedirects(response, reverse('billing:invoice_list'))
        payment = Payment.objects.get(ad=self.ad)
        self.assertEqual(payment.status, 'manual_review')
        self.assertEqual(payment.gateway, 'zarinpal')
        self.assertIn('پیکربندی', payment.admin_note)
