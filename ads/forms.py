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
            'category', 'title', 'description', 'price', 'phone',
            'location_country', 'location_province', 'location_city',
            'is_negotiable', 'url'
        ]
        widgets = {
            'category': forms.Select(attrs={'class': 'form-control'}),
            'title': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'عنوان آگهی',
                'maxlength': 100
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
            'phone': forms.TextInput(attrs={
                'class': 'form-control',
                'placeholder': 'شماره تماس',
                'dir': 'ltr'
            }),
            'location_country': forms.Select(attrs={'class': 'form-control'}),
            'location_province': forms.Select(attrs={'class': 'form-control'}),
            'location_city': forms.Select(attrs={'class': 'form-control'}),
            'is_negotiable': forms.CheckboxInput(attrs={'class': 'form-check-input'}),
            'url': forms.URLInput(attrs={
                'class': 'form-control',
                'placeholder': 'لینک وب‌سایت (اختیاری)',
                'dir': 'ltr'
            }),
        }
        labels = {
            'category': 'دسته‌بندی',
            'title': 'عنوان آگهی',
            'description': 'توضیحات',
            'price': 'قیمت (تومان)',
            'phone': 'شماره تماس',
            'location_country': 'کشور',
            'location_province': 'استان',
            'location_city': 'شهر',
            'is_negotiable': 'قیمت توافقی',
            'url': 'وب‌سایت',
        }


class AdImageForm(forms.Form):
    """Ad image upload form."""
    
    images = forms.FileField(
        label='تصاویر آگهی',
        widget=forms.ClearableFileInput(attrs={
            'multiple': True,
            'accept': 'image/*',
            'class': 'form-control'
        }),
        required=False
    )
