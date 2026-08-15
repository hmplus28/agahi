from django.test import TestCase
from django.urls import reverse

from .models import User


class AccountTests(TestCase):
    def test_registration_creates_mobile_username_and_profile(self):
        response = self.client.post(reverse('accounts:register'), {
            'mobile': '۰۹۱۲۳۴۵۶۷۸۹',
            'password1': 'secure-password-123',
            'password2': 'secure-password-123',
        })
        self.assertRedirects(response, reverse('dashboard:index'))
        user = User.objects.get(mobile='09123456789')
        self.assertEqual(user.username, user.mobile)
        self.assertTrue(hasattr(user, 'profile'))

    def test_login_accepts_mobile_username(self):
        User.objects.create_user(username='09125555555', mobile='09125555555', password='secure-password-123')
        response = self.client.post(reverse('accounts:login'), {
            'username': '09125555555',
            'password': 'secure-password-123',
        })
        self.assertRedirects(response, reverse('dashboard:index'))
