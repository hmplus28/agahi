"""Print the effective network-sensitive Django settings for offline deployment verification."""
import json
import os
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'config.settings.production')

import django  # noqa: E402

django.setup()

from django.conf import settings  # noqa: E402

print(json.dumps({
    'offline_mode': settings.OFFLINE_MODE,
    'cache_backend': settings.CACHES['default']['BACKEND'],
    'cache_location': str(settings.CACHES['default']['LOCATION']),
    'email_backend': settings.EMAIL_BACKEND,
    'sms_enabled': settings.SMS_ENABLED,
}, ensure_ascii=False, indent=2))
