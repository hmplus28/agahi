"""Forms for creating and editing advertisements."""
import re

from django import forms
from django.core.exceptions import ValidationError

from core.models import SiteSettings
from core.services.duplicate_detection import detect_duplicate_ads
from core.services.persian_normalization import normalize_persian_text
from locations.models import City, Country, Province
from taxonomy.models import Category

from .models import Ad, AdReport, ForbiddenWord


class MultipleFileInput(forms.ClearableFileInput):
    allow_multiple_selected = True


class MultipleImageField(forms.FileField):
    widget = MultipleFileInput

    def clean(self, data, initial=None):
        if not data:
            return []
        files = data if isinstance(data, (list, tuple)) else [data]
        return [super().clean(item, initial) for item in files]


class AdForm(forms.ModelForm):
    """Validate public ad data consistently for create and edit flows."""

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
            'phone_1': forms.TextInput(attrs={'class': 'form-control', 'dir': 'ltr', 'maxlength': 20}),
            'phone_2': forms.TextInput(attrs={'class': 'form-control', 'dir': 'ltr', 'maxlength': 20}),
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

    def __init__(self, *args, allow_duplicate=False, **kwargs):
        """Allow a specifically-authorized workflow to override duplicate blocking."""
        self.allow_duplicate = allow_duplicate
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

    def _validate_forbidden_words(self, cleaned_data):
        """Reject configured forbidden expressions in every user-editable text field."""
        haystack = normalize_persian_text(' '.join([
            cleaned_data.get('title', ''),
            cleaned_data.get('description', ''),
            cleaned_data.get('keywords', ''),
            cleaned_data.get('business_name', ''),
        ])).casefold()
        if not haystack:
            return
        forbidden_words = ForbiddenWord.objects.filter(is_active=True).only('word', 'normalized_word')
        for forbidden in forbidden_words:
            normalized_word = normalize_persian_text(forbidden.normalized_word or forbidden.word).casefold()
            if not normalized_word:
                continue
            pattern = rf'(?<!\w){re.escape(normalized_word)}(?!\w)'
            if re.search(pattern, haystack):
                raise ValidationError('متن آگهی شامل عبارت غیرمجاز «%s» است.' % forbidden.word)

    def _validate_duplicate(self, cleaned_data):
        """Block likely duplicate ads while excluding the record currently being edited."""
        if self.allow_duplicate or not cleaned_data.get('title') or not cleaned_data.get('description'):
            return
        is_duplicate, _duplicates = detect_duplicate_ads(
            title=cleaned_data['title'],
            description=cleaned_data['description'],
            mobile=cleaned_data.get('mobile_1', ''),
            category_id=getattr(cleaned_data.get('category'), 'pk', None),
            city_id=getattr(cleaned_data.get('city'), 'pk', None),
            exclude_ad_id=self.instance.pk or None,
        )
        if is_duplicate:
            raise ValidationError(
                'آگهی مشابهی اخیراً ثبت شده است. لطفاً پیش از ارسال، آگهی‌های موجود را بررسی کنید.'
            )

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
        if not self.errors:
            self._validate_forbidden_words(cleaned)
            self._validate_duplicate(cleaned)
        return cleaned


class AdImageForm(forms.Form):
    """Receive optional new images while keeping persistence in the service layer."""

    images = MultipleImageField(
        label='تصاویر آگهی',
        required=False,
        widget=MultipleFileInput(attrs={'accept': 'image/jpeg,image/png,image/webp', 'class': 'form-control'}),
    )

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        settings_obj = SiteSettings.objects.only('max_images').first()
        max_images = settings_obj.max_images if settings_obj else 5
        self.fields['images'].help_text = f'حداکثر {max_images} تصویر با فرمت JPEG، PNG یا WebP انتخاب کنید.'

    def clean_images(self):
        images = self.cleaned_data['images']
        settings_obj = SiteSettings.objects.only('max_images').first()
        max_images = settings_obj.max_images if settings_obj else 5
        if len(images) > max_images:
            raise ValidationError(f'حداکثر {max_images} تصویر را می‌توانید هم‌زمان انتخاب کنید.')
        return images


class AdPermitForm(forms.Form):
    """Owner-facing submission form for a permit requested by moderation."""

    permit_number = forms.CharField(label='شماره مجوز', max_length=100, required=False, widget=forms.TextInput(attrs={'class': 'form-control'}))
    issuer = forms.CharField(label='مرجع صدور', max_length=255, required=False, widget=forms.TextInput(attrs={'class': 'form-control'}))
    issued_at = forms.DateField(label='تاریخ صدور', required=False, widget=forms.DateInput(attrs={'class': 'form-control', 'type': 'date'}))
    image = forms.ImageField(label='تصویر مجوز', widget=forms.ClearableFileInput(attrs={'class': 'form-control', 'accept': 'image/jpeg,image/png,image/webp'}))


class AdLinkForm(forms.Form):
    """Simple bounded form for adding one user-generated external link."""

    type = forms.ChoiceField(label='نوع لینک', choices=Ad.LINK_TYPES if hasattr(Ad, 'LINK_TYPES') else [], required=False)
    url = forms.URLField(label='نشانی لینک', max_length=500, required=False, widget=forms.URLInput(attrs={'class': 'form-control', 'dir': 'ltr'}))

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        from .models import AdLink
        self.fields['type'].choices = AdLink.LINK_TYPES

    def clean(self):
        cleaned = super().clean()
        if bool(cleaned.get('type')) != bool(cleaned.get('url')):
            raise ValidationError('برای ثبت لینک، نوع و نشانی را با هم وارد کنید.')
        return cleaned


class AdReportForm(forms.Form):
    """Minimal public report form; reporter identity is captured server-side when available."""

    reason = forms.ChoiceField(label='دلیل گزارش', choices=AdReport.REASON_CHOICES)
    description = forms.CharField(
        label='توضیحات',
        required=False,
        max_length=1000,
        widget=forms.Textarea(attrs={'class': 'form-control', 'rows': 3}),
    )
