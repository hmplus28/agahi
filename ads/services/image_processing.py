"""Safe image processing for advertisement and permit uploads."""
from io import BytesIO
from uuid import uuid4

from django.core.exceptions import ValidationError
from django.core.files.base import ContentFile
from PIL import Image, ImageOps, UnidentifiedImageError

from ads.models import AdImage

SUPPORTED_FORMATS = {'JPEG', 'PNG', 'WEBP'}
MAX_UPLOAD_BYTES = 10 * 1024 * 1024
MAX_SOURCE_PIXELS = 25_000_000
DISPLAY_MAX_SIZE = (1280, 1280)
THUMB_MAX_SIZE = (480, 480)
WEBP_QUALITY = 80


def _validate_upload(uploaded_file):
    if uploaded_file.size > MAX_UPLOAD_BYTES:
        raise ValidationError('حجم هر تصویر حداکثر ۱۰ مگابایت است.')
    try:
        image = Image.open(uploaded_file)
        image.verify()
        uploaded_file.seek(0)
        image = Image.open(uploaded_file)
    except (UnidentifiedImageError, OSError, Image.DecompressionBombError) as exc:
        raise ValidationError('فایل انتخاب‌شده یک تصویر معتبر نیست.') from exc
    if image.format not in SUPPORTED_FORMATS:
        raise ValidationError('فقط تصویرهای JPEG، PNG و WebP پذیرفته می‌شوند.')
    if image.width * image.height > MAX_SOURCE_PIXELS:
        raise ValidationError('ابعاد تصویر برای پردازش ایمن بیش از حد بزرگ است.')
    return image


def _normalize_image(image):
    """Correct orientation and strip metadata by creating a clean RGB image."""
    image = ImageOps.exif_transpose(image)
    if image.mode in {'RGBA', 'LA'}:
        background = Image.new('RGB', image.size, 'white')
        background.paste(image, mask=image.getchannel('A'))
        return background
    if image.mode == 'P':
        return image.convert('RGBA').convert('RGB')
    if image.mode != 'RGB':
        return image.convert('RGB')
    return image


def _resized_webp(source, max_size):
    image = source.copy()
    image.thumbnail(max_size, Image.Resampling.LANCZOS)
    output = BytesIO()
    image.save(output, format='WEBP', quality=WEBP_QUALITY, method=6)
    output.seek(0)
    return image, ContentFile(output.read())


def save_ad_image(ad, uploaded_file, *, sort_order=0, is_primary=False):
    """Validate an upload and persist only optimized thumbnail/display WebP files."""
    source = _normalize_image(_validate_upload(uploaded_file))
    display_image, display_file = _resized_webp(source, DISPLAY_MAX_SIZE)
    thumb_image, thumb_file = _resized_webp(source, THUMB_MAX_SIZE)
    stem = f'ad-{ad.code}-{sort_order + 1}'
    return AdImage.objects.create(
        ad=ad,
        image_thumb=ContentFile(thumb_file.read(), name=f'{stem}-thumb.webp'),
        image_display=ContentFile(display_file.read(), name=f'{stem}-display.webp'),
        thumb_width=thumb_image.width,
        thumb_height=thumb_image.height,
        display_width=display_image.width,
        display_height=display_image.height,
        sort_order=sort_order,
        is_primary=is_primary,
    )


def save_permit_image(ad, uploaded_file):
    """Return a sanitized WebP permit image without persisting the uploaded original."""
    source = _normalize_image(_validate_upload(uploaded_file))
    _image, optimized_file = _resized_webp(source, DISPLAY_MAX_SIZE)
    return ContentFile(optimized_file.read(), name=f'permit-{ad.code}-{uuid4().hex[:12]}.webp')


def validate_ad_image(uploaded_file):
    """Compatibility helper returning validation outcome for form integrations."""
    try:
        _validate_upload(uploaded_file)
    except ValidationError as exc:
        return False, exc.messages[0]
    finally:
        uploaded_file.seek(0)
    return True, None
