"""Forms for creating and editing advertisements."""
import re

from django import forms
from django.core.exceptions import ValidationError

from core.services.persian_normalization import normalize_persian_text
from locations.models import City, Country, Province
from taxonomy.models import Category

from .models import Ad


class MultipleFileInput(forms.ClearableFileInput):
    allow_multiple_selected = True


class MultipleImageField(forms.FileField):
    widget = MultipleFileInput

    def clean(self, data, initial=None):
        if not data:
            return []
        files = data if isinstance(data, (list, tuple)) else [data]
        return [super(MultipleImageField, self).clean(item, initial) for item in files]


class AdForm(forms.ModelForm):
    """Validate the public fields allowed in an advertisement workflow."""

    class Meta:
        model = Ad
        fields = [
            'category', 'title', 'description', 'keywords', 'price', 'country',
            'province', 'city', 'address', 'mobile_1', 'show_mobile_1',
            'mobile_2', 'phone_1', 'phone_2', 'email', 'full_name',
            'business_name',
        ]
        widgets = {
            'category': forms.Select(attrs={'class': 'form-control'}),
            'title': forms.TextInput(attrs={'class': 'form-control', 'maxlength': 300}),
            'description': forms.Textarea(attrs={'class': 'form-control', 'rows': 8, 'maxlength': 6000}),
            'keywords': forms.TextInput(attrs={'class': 'form-control', 'placeholder': 'با کاما جدا کنید'}),
            'price': forms.NumberInput(attrs={'class': 'form-control', 'min': 0}),
            'country': forms.Select(attrs={'class': 'form-control'}),
            'province': forms.Select(attrs={'class': 'form-control'}),
            'city': forms.Select(attrs={'class': 'form-control'}),
            'address': forms.Textarea(attrs={'class': 'form-control', 'rows': 3}),
            'mobile_1': forms.TextInput(attrs={'class': 'form-control', 'dir': 'ltr', 'maxlength': 11}),
            'mobile_2': forms.TextInput(attrs={'class': 'form-control', 'dir': 'ltr', 'maxlength': 11}),
            'phone_1': forms.TextInput(attrs={'class': 'form-control', 'dir': 'ltr'}),
            'phone_2': forms.TextInput(attrs={'class': 'form-control', 'dir': 'ltr'}),
            'email': forms.EmailInput(attrs={'class': 'form-control', 'dir': 'ltr'}),
            'full_name': forms.TextInput(attrs={'class': 'form-control'}),
            'business_name': forms.TextInput(attrs={'class': 'form-control'}),
        }
        labels = {
            'category': 'دسته‌بندی', 'title': 'عنوان آگهی', 'description': 'توضیحات',
            'keywords': 'کلیدواژه‌ها', 'price': 'قیمت (تومان)', 'country': 'کشور',
            'province': 'استان', 'city': 'شهر', 'address': 'نشانی',
            'mobile_1': 'موبایل اصلی', 'show_mobile_1': 'نمایش موبایل اصلی',
            'mobile_2': 'موبایل دوم', 'phone_1': 'تلفن ۱', 'phone_2': 'تلفن ۲',
            'email': 'ایمیل', 'full_name': 'نام و نام خانوادگی',
            'business_name': 'نام کسب‌وکار',
        }

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields['category'].queryset = Category.objects.filter(is_active=True).order_by('sort_order', 'title')
        self.fields['country'].queryset = Country.objects.filter(is_active=True).order_by('sort_order', 'name')
        self.fields['province'].queryset = Province.objects.filter(is_active=True).select_related('country').order_by('sort_order', 'name')
        self.fields['city'].queryset = City.objects.filter(is_active=True).select_related('province').order_by('sort_order', 'name')

    def clean_title(self):
        return normalize_persian_text(self.cleaned_data['title'])

    def clean_description(self):
        return normalize_persian_text(self.cleaned_data['description'])

    def clean_keywords(self):
        raw = self.cleaned_data.get('keywords', '')
        values = [normalize_persian_text(value) for value in raw.split(',') if value.strip()]
        unique_values = list(dict.fromkeys(values))
        if len(unique_values) > 11:
            raise ValidationError('حداکثر ۱۱ کلیدواژه مجاز است.')
        return ', '.join(unique_values)

    def clean_mobile_1(self):
        return self._clean_mobile(self.cleaned_data['mobile_1'], required=True)

    def clean_mobile_2(self):
        return self._clean_mobile(self.cleaned_data.get('mobile_2', ''), required=False)

    @staticmethod
    def _clean_mobile(value, required=False):
        value = re.sub(r'[^0-9]', '', value or '')
        if not value and not required:
            return ''
        if not re.fullmatch(r'09\d{9}', value):
            raise ValidationError('شماره موبایل باید با ۰۹ شروع و ۱۱ رقم باشد.')
        return value

    def clean(self):
        cleaned = super().clean()
        country = cleaned.get('country')
        province = cleaned.get('province')
        city = cleaned.get('city')
        if city and province and city.province_id != province.pk:
            self.add_error('city', 'شهر انتخاب‌شده متعلق به استان انتخاب‌شده نیست.')
        if province and country and province.country_id != country.pk:
            self.add_error('province', 'استان انتخاب‌شده متعلق به کشور انتخاب‌شده نیست.')
        if not city:
            self.add_error('city', 'انتخاب شهر الزامی است.')
        return cleaned


class AdImageForm(forms.Form):
    """Receive optional new images while keeping upload handling in the view."""

    images = MultipleImageField(
        label='تصاویر آگهی',
        required=False,
        widget=MultipleFileInput(attrs={'accept': 'image/jpeg,image/png,image/webp', 'class': 'form-control'}),
        help_text='حداکثر پنج تصویر با فرمت JPEG، PNG یا WebP انتخاب کنید.',
    )
