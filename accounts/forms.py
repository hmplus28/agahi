"""
Accounts app forms.
"""
import re
from django import forms
from django.contrib.auth.forms import UserCreationForm, AuthenticationForm
from .models import User, Profile


class RegisterForm(UserCreationForm):
    """User registration form."""
    
    mobile = forms.CharField(
        label='شماره موبایل',
        max_length=11,
        widget=forms.TextInput(attrs={
            'class': 'form-control',
            'placeholder': '09xxxxxxxxx',
            'dir': 'ltr'
        })
    )
    
    class Meta:
        model = User
        fields = ['mobile', 'password1', 'password2']
    
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields['password1'].label = 'رمز عبور'
        self.fields['password2'].label = 'تکرار رمز عبور'
        self.fields['password1'].widget.attrs['class'] = 'form-control'
        self.fields['password2'].widget.attrs['class'] = 'form-control'
    
    def clean_mobile(self):
        mobile = self.cleaned_data.get('mobile')
        
        # Validate Iranian mobile number
        if not re.match(r'^09\d{9}$', mobile):
            raise forms.ValidationError('شماره موبایل باید معتبر باشد (مثال: 09123456789)')
        
        # Check if mobile already exists
        if User.objects.filter(mobile=mobile).exists():
            raise forms.ValidationError('این شماره موبایل قبلاً ثبت شده است.')
        
        return mobile
    
    def save(self, commit=True):
        user = super().save(commit=commit)
        user.username = user.mobile  # Use mobile as username
        if commit:
            user.save()
        return user


class LoginForm(AuthenticationForm):
    """User login form."""
    
    username = forms.CharField(
        label='شماره موبایل',
        max_length=11,
        widget=forms.TextInput(attrs={
            'class': 'form-control',
            'placeholder': '09xxxxxxxxx',
            'dir': 'ltr'
        })
    )
    
    password = forms.CharField(
        label='رمز عبور',
        widget=forms.PasswordInput(attrs={
            'class': 'form-control',
            'dir': 'ltr'
        })
    )
    
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields['username'].label = 'شماره موبایل'
        self.fields['username'].help_text = None
