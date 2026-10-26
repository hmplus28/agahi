#!/usr/bin/env python3
import json
import sys
from pathlib import Path

source = Path(sys.argv[1])
data = json.loads(source.read_text())

categories = data.get('categories', {})
audits = data.get('audits', {})
metrics = [
    ('first-contentful-paint', 'FCP'),
    ('largest-contentful-paint', 'LCP'),
    ('speed-index', 'Speed Index'),
    ('total-blocking-time', 'TBT'),
    ('cumulative-layout-shift', 'CLS'),
    ('interactive', 'TTI'),
]

print('category,score_percent')
for key in ('performance', 'accessibility', 'seo'):
    score = categories.get(key, {}).get('score')
    print(f'{key},{round(score * 100) if score is not None else "n/a"}')

print('\nmetric,id,numeric_value,display_value')
for audit_id, label in metrics:
    audit = audits.get(audit_id, {})
    print(f'{label},{audit_id},{audit.get("numericValue", "n/a")},{audit.get("displayValue", "n/a")}')

print('\nseo_audit,id,score,title')
for audit_id in categories.get('seo', {}).get('auditRefs', []):
    audit = audits.get(audit_id['id'], {})
    print(f'{audit_id["id"]},{audit.get("score", "n/a")},{audit.get("title", "")}')
