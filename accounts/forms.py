"""Account forms for mobile-first authentication."""
import re

from django import forms
from django.contrib.auth.forms import AuthenticationForm, UserCreationForm

from .models import User


class RegisterForm(UserCreationForm):
    mobile = forms.CharField(
        label='شماره موبایل',
        max_length=11,
        widget=forms.TextInput(attrs={'class': 'form-control', 'placeholder': '09123456789', 'dir': 'ltr'}),
    )

    class Meta:
        model = User
        fields = ['mobile', 'password1', 'password2']

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields['password1'].label = 'رمز عبور'
        self.fields['password2'].label = 'تکرار رمز عبور'
        for field_name in ('password1', 'password2'):
            self.fields[field_name].widget.attrs['class'] = 'form-control'

    def clean_mobile(self):
        mobile = (self.cleaned_data.get('mobile') or '').translate(str.maketrans('۰۱۲۳۴۵۶۷۸۹', '0123456789'))
        mobile = re.sub(r'\D', '', mobile)
        if not re.fullmatch(r'09\d{9}', mobile):
            raise forms.ValidationError('شماره موبایل باید با ۰۹ شروع و ۱۱ رقم باشد.')
        if User.objects.filter(mobile=mobile).exists():
            raise forms.ValidationError('این شماره موبایل قبلاً ثبت شده است.')
        return mobile

    def save(self, commit=True):
        user = super().save(commit=False)
        user.mobile = self.cleaned_data['mobile']
        user.username = user.mobile
        if commit:
            user.save()
        return user


class LoginForm(AuthenticationForm):
    """Django's authentication form presented with mobile terminology."""

    username = forms.CharField(
        label='شماره موبایل',
        max_length=11,
        widget=forms.TextInput(attrs={'class': 'form-control', 'placeholder': '09123456789', 'dir': 'ltr'}),
    )
    password = forms.CharField(
        label='رمز عبور',
        widget=forms.PasswordInput(attrs={'class': 'form-control', 'dir': 'ltr'}),
    )

    def clean_username(self):
        value = self.cleaned_data['username'].translate(str.maketrans('۰۱۲۳۴۵۶۷۸۹', '0123456789'))
        return re.sub(r'\D', '', value)
