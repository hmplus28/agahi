#!/usr/bin/env python3
import json
import sys
from pathlib import Path

report = json.loads(Path(sys.argv[1]).read_text())
for audit_id, audit in report.get('audits', {}).items():
    score = audit.get('score')
    details = audit.get('details', {})
    if score is not None and score < 1 and (audit.get('numericValue', 0) or details.get('overallSavingsMs', 0)):
        print(f"{audit_id}\tscore={score}\tvalue={audit.get('numericValue')}\tsavings_ms={details.get('overallSavingsMs')}\t{audit.get('title')}")
