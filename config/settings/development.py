"""
Development settings for config project.
"""

from .base import *

# SECURITY WARNING: don't run with debug turned on in production!
DEBUG = True

ALLOWED_HOSTS = ['localhost', '127.0.0.1']

# For development, you can use SQLite for simplicity
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME': BASE_DIR / 'db.sqlite3',
    }
}

# If PostgreSQL is available, use it instead:
# DATABASES = {
#     'default': {
#         'ENGINE': 'django.db.backends.postgresql',
#         'NAME': os.environ.get('DATABASE_NAME', 'agahi_db'),
#         'USER': os.environ.get('DATABASE_USER', 'agahi_user'),
#         'PASSWORD': os.environ.get('DATABASE_PASSWORD', ''),
#         'HOST': os.environ.get('DATABASE_HOST', 'localhost'),
#         'PORT': os.environ.get('DATABASE_PORT', '5432'),
#         'CONN_MAX_AGE': 600,
#         'OPTIONS': {
#             'connect_timeout': 10,\n#         },
#     }
# }

# Email backend for development
EMAIL_BACKEND = 'django.core.mail.backends.console.EmailBackend'

# Disable CSRF checks for development with HTMX or API testing
# CSRF_TRUSTED_ORIGINS = []

# Show all logs
LOGGING = {
    'version': 1,
    'disable_existing_loggers': False,
    'handlers': {
        'console': {
            'class': 'logging.StreamHandler',
        },
    },
    'root': {
        'handlers': ['console'],
        'level': 'DEBUG',
    },
}

# Django Debug Toolbar (optional - install separately)
# INSTALLED_APPS += ['debug_toolbar']
# MIDDLEWARE.insert(0, 'debug_toolbar.middleware.DebugToolbarMiddleware')
# INTERNAL_IPS = ['127.0.0.1']
