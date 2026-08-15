"""Measure in-process Django response latency for public endpoints without mutating database state."""
import json
import os
import statistics
import sys
import time
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'config.settings.development')

import django  # noqa: E402

django.setup()

from django.test import Client  # noqa: E402


ITERATIONS = 30
ENDPOINTS = ('/', '/ads/', '/ads/?q=تست', '/healthz/', '/robots.txt', '/sitemap.xml')


def percentile(values, ratio):
    if not values:
        return 0.0
    ordered = sorted(values)
    index = max(0, min(len(ordered) - 1, round((len(ordered) - 1) * ratio)))
    return ordered[index]


def measure(client, url):
    started = time.perf_counter()
    response = client.get(url, HTTP_ACCEPT_ENCODING='gzip')
    elapsed = (time.perf_counter() - started) * 1000
    return response, elapsed


def main():
    client = Client(HTTP_HOST='localhost')
    results = []
    for endpoint in ENDPOINTS:
        first_response, cold_ms = measure(client, endpoint)
        warm_samples = [measure(client, endpoint)[1] for _ in range(ITERATIONS)]
        results.append({
            'endpoint': endpoint,
            'status': first_response.status_code,
            'bytes': len(first_response.content),
            'gzip': first_response.get('Content-Encoding') == 'gzip',
            'cold_ms': round(cold_ms, 2),
            'warm_p50_ms': round(statistics.median(warm_samples), 2),
            'warm_p95_ms': round(percentile(warm_samples, 0.95), 2),
            'warm_max_ms': round(max(warm_samples), 2),
        })
    print(json.dumps({'iterations_per_endpoint': ITERATIONS, 'results': results}, ensure_ascii=False, indent=2))


if __name__ == '__main__':
    main()
