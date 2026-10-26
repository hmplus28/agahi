#!/usr/bin/env python3
import json
import sys
from pathlib import Path

report = json.loads(Path(sys.argv[1]).read_text())
category = sys.argv[2]
audits = report.get('audits', {})
for reference in report.get('categories', {}).get(category, {}).get('auditRefs', []):
    audit = audits.get(reference['id'], {})
    if audit.get('score') not in (1, None):
        print(f"{reference['id']}\tscore={audit.get('score')}\t{audit.get('title')}\t{audit.get('description', '')}")
