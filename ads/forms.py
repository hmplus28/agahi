"""
Ads app forms.
"""
from django import forms
from .models import Ad, AdImage


class AdForm(forms.ModelForm):
    """Ad creation/edit form."""
    
    class Meta:
        model = Ad
        fields = [
            'category', 'title', 'description', 'price',
            'country', 'province', 'city',
            'mobile_1', 'show_mobile_1', 'mobile_2', 'phone_1', 'phone_2', 'email',
            'full_name', 'business_name',
        ]
        widgets = {
            'category': forms.Select(attrs={'class': 'form-control'}),
            'title': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'عنوان آگهی',
                'maxlength': 300
            }),
            'description': forms.Textarea(attrs={
                'class': 'form-control',
                'placeholder': 'توضیحات آگهی',
                'rows': 6
            }),
            'price': forms.NumberInput(attrs={
                'class': 'form-control',
                'placeholder': 'قیمت (تومان)',
                'min': 0
            }),
            'country': forms.Select(attrs={'class': 'form-control'}),
            'province': forms.Select(attrs={'class': 'form-control'}),
            'city': forms.Select(attrs={'class': 'form-control'}),
            'mobile_1': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'شماره موبایل اصلی',
                'dir': 'ltr'
            }),
            'show_mobile_1': forms.CheckboxInput(attrs={'class': 'form-check-input'}),
            'mobile_2': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'شماره موبایل دوم',
                'dir': 'ltr'
            }),
            'phone_1': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'شماره تلفن ۱',
                'dir': 'ltr'
            }),
            'phone_2': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'شماره تلفن ۲',
                'dir': 'ltr'
            }),
            'email': forms.EmailInput(attrs={
                'class': 'form-control',
                'placeholder': 'ایمیل',
                'dir': 'ltr'
            }),
            'full_name': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'نام و نام خانوادگی'
            }),
            'business_name': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'نام کسب‌وکار'
            }),
        }
        labels = {
            'category': 'دسته‌بندی',
            'title': 'عنوان آگهی',
            'description': 'توضیحات',
            'price': 'قیمت (تومان)',
            'country': 'کشور',
            'province': 'استان',
            'city': 'شهر',
            'mobile_1': 'موبایل ۱',
            'show_mobile_1': 'نمایش موبایل ۱',
            'mobile_2': 'موبایل ۲',
            'phone_1': 'تلفن ۱',
            'phone_2': 'تلفن ۲',
            'email': 'ایمیل',
            'full_name': 'نام و نام خانوادگی',
            'business_name': 'نام کسب‌وکار',
        }


class AdImageForm(forms.Form):
    """Ad image upload form."""
    
    images = forms.FileField(
        label='تصاویر آگهی',
        widget=forms.ClearableFileInput(attrs={
            'accept': 'image/*',
            'class': 'form-control'
        }),
        required=False,
        help_text='می‌توانید چندین تصویر را انتخاب کنید'
    )
