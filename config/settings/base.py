"""Shared Django settings for the classifieds project."""
from pathlib import Path
import os

from dotenv import load_dotenv

BASE_DIR = Path(__file__).resolve().parent.parent.parent
load_dotenv(BASE_DIR / '.env')

SECRET_KEY = os.environ.get('SECRET_KEY', '')
DEBUG = os.environ.get('DEBUG', 'false').lower() in {'1', 'true', 'yes', 'on'}

_raw_hosts = os.environ.get('ALLOWED_HOSTS', '')
ALLOWED_HOSTS = [host.strip() for host in _raw_hosts.split(',') if host.strip()]

INSTALLED_APPS = [
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
    'django.contrib.postgres',
    'core',
    'accounts',
    'ads',
    'taxonomy',
    'locations',
    'billing',
    'moderation',
    'support',
    'notifications',
    'seo',
    'dashboard',
    'pages',
]

MIDDLEWARE = [
    'django.middleware.gzip.GZipMiddleware',
    'django.middleware.security.SecurityMiddleware',
    'core.security.PublicSecurityHeadersMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.http.ConditionalGetMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
]

ROOT_URLCONF = 'config.urls'

TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [BASE_DIR / 'templates'],
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
                'django.template.context_processors.i18n',
                'django.template.context_processors.static',
                'core.context_processors.site_settings',
            ],
        },
    },
]

WSGI_APPLICATION = 'config.wsgi.application'
ASGI_APPLICATION = 'config.asgi.application'

_cache_url = os.environ.get('CACHE_URL', '')
_cache_dir = os.environ.get('CACHE_DIR', '')
if _cache_url:
    CACHES = {
        'default': {
            'BACKEND': 'django.core.cache.backends.redis.RedisCache',
            'LOCATION': _cache_url,
            'TIMEOUT': 300,
            'KEY_PREFIX': 'agahi',
        }
    }
elif _cache_dir:
    CACHES = {
        'default': {
            'BACKEND': 'django.core.cache.backends.filebased.FileBasedCache',
            'LOCATION': _cache_dir,
            'TIMEOUT': 300,
            'OPTIONS': {'MAX_ENTRIES': 5000, 'CULL_FREQUENCY': 3},
            'KEY_PREFIX': 'agahi',
        }
    }
else:
    CACHES = {
        'default': {
            'BACKEND': 'django.core.cache.backends.locmem.LocMemCache',
            'LOCATION': 'agahi-local-cache',
            'TIMEOUT': 300,
            'OPTIONS': {'MAX_ENTRIES': 2000, 'CULL_FREQUENCY': 3},
            'KEY_PREFIX': 'agahi',
        }
    }

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.postgresql',
        'NAME': os.environ.get('DATABASE_NAME', 'agahi_db'),
        'USER': os.environ.get('DATABASE_USER', 'agahi_user'),
        'PASSWORD': os.environ.get('DATABASE_PASSWORD', ''),
        'HOST': os.environ.get('DATABASE_HOST', 'localhost'),
        'PORT': os.environ.get('DATABASE_PORT', '5432'),
        'CONN_MAX_AGE': int(os.environ.get('DB_CONN_MAX_AGE', '600')),
        'OPTIONS': {'connect_timeout': 10},
    }
}

AUTH_PASSWORD_VALIDATORS = [
    {'NAME': 'django.contrib.auth.password_validation.UserAttributeSimilarityValidator'},
    {'NAME': 'django.contrib.auth.password_validation.MinimumLengthValidator'},
    {'NAME': 'django.contrib.auth.password_validation.CommonPasswordValidator'},
    {'NAME': 'django.contrib.auth.password_validation.NumericPasswordValidator'},
]

LANGUAGE_CODE = 'fa-ir'
TIME_ZONE = 'Asia/Tehran'
USE_I18N = True
USE_TZ = True

STATIC_URL = '/static/'
STATIC_ROOT = BASE_DIR / 'staticfiles'
STATICFILES_DIRS = [BASE_DIR / 'static']
MEDIA_URL = '/media/'
MEDIA_ROOT = BASE_DIR / 'media'

DEFAULT_AUTO_FIELD = 'django.db.models.BigAutoField'
AUTH_USER_MODEL = 'accounts.User'

SESSION_COOKIE_AGE = 1209600
SESSION_SAVE_EVERY_REQUEST = False
SESSION_COOKIE_HTTPONLY = True
CSRF_COOKIE_HTTPONLY = True

SECURE_CONTENT_TYPE_NOSNIFF = True
X_FRAME_OPTIONS = 'DENY'

LOGIN_URL = 'accounts:login'
LOGIN_REDIRECT_URL = 'dashboard:index'
LOGOUT_REDIRECT_URL = 'core:home'

DEFAULT_FROM_EMAIL = os.environ.get('DEFAULT_FROM_EMAIL', 'no-reply@example.com')
EMAIL_BACKEND = os.environ.get(
    'EMAIL_BACKEND', 'django.core.mail.backends.console.EmailBackend'
)

SITE_URL = os.environ.get('SITE_URL', 'http://localhost:8000').rstrip('/')
SEO_LANDING_MIN_ADS = int(os.environ.get('SEO_LANDING_MIN_ADS', '3'))
DEFAULT_PAYMENT_GATEWAY = os.environ.get('DEFAULT_PAYMENT_GATEWAY', 'zarinpal')
ZARINPAL_MERCHANT_ID = os.environ.get('ZARINPAL_MERCHANT_ID', '')
ZARINPAL_SANDBOX = os.environ.get('PAYMENT_SANDBOX', 'true').lower() in {'1', 'true', 'yes'}
NEXTPAY_API_KEY = os.environ.get('NEXTPAY_API_KEY', '')
NEXTPAY_SANDBOX = os.environ.get('NEXTPAY_SANDBOX', 'true').lower() in {'1', 'true', 'yes'}
PAYMENT_HTTP_TIMEOUT = int(os.environ.get('PAYMENT_HTTP_TIMEOUT', '20'))
SMS_ENABLED = os.environ.get('SMS_ENABLED', 'false').lower() in {'1', 'true', 'yes'}
SMS_PROVIDER = os.environ.get('SMS_PROVIDER', 'disabled')
SMS_SENDER_ID = os.environ.get('SMS_SENDER_ID', '')
SMS_HTTP_TIMEOUT = int(os.environ.get('SMS_HTTP_TIMEOUT', '15'))
SMS_MAX_ATTEMPTS = int(os.environ.get('SMS_MAX_ATTEMPTS', '3'))
KAVENEGAR_API_KEY = os.environ.get('KAVENEGAR_API_KEY', '')

# Backup: local gzip is always available; encryption/remote transfer require explicit credentials.
BACKUP_DIR = os.environ.get('BACKUP_DIR', str(BASE_DIR / 'var' / 'backups'))
BACKUP_RETENTION_DAYS = int(os.environ.get('BACKUP_RETENTION_DAYS', '30'))
BACKUP_RETENTION_COUNT = int(os.environ.get('BACKUP_RETENTION_COUNT', '30'))
BACKUP_ENCRYPTION_KEY = os.environ.get('BACKUP_ENCRYPTION_KEY', '')
BACKUP_REMOTE_PROVIDER = os.environ.get('BACKUP_REMOTE_PROVIDER', '')
BACKUP_REMOTE_HOST = os.environ.get('BACKUP_REMOTE_HOST', '')
BACKUP_REMOTE_PORT = int(os.environ.get('BACKUP_REMOTE_PORT', '21'))
BACKUP_REMOTE_USERNAME = os.environ.get('BACKUP_REMOTE_USERNAME', '')
BACKUP_REMOTE_PASSWORD = os.environ.get('BACKUP_REMOTE_PASSWORD', '')
BACKUP_REMOTE_PATH = os.environ.get('BACKUP_REMOTE_PATH', '')
BACKUP_PG_DUMP_BIN = os.environ.get('BACKUP_PG_DUMP_BIN', 'pg_dump')
BACKUP_PSQL_BIN = os.environ.get('BACKUP_PSQL_BIN', 'psql')
BACKUP_OPENSSL_BIN = os.environ.get('BACKUP_OPENSSL_BIN', 'openssl')

# This project performs Jalali conversion only at presentation time; database
# timestamps remain timezone-aware Gregorian values.
JALALI_DATE_DEFAULTS = {
    'LIST_DISPLAY_AUTO_CONVERT': False,
    'Strftime': {'date': '%y/%m/%d', 'datetime': '%H:%M:%S _ %y/%m/%d'},
}
