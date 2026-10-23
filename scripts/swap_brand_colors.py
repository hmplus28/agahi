#!/usr/bin/env python3
"""One-shot brand color swap: teal family -> Shetab indigo family,
post-ad orange -> Shetab red. Token-for-token swap; layout untouched."""
import re
from pathlib import Path

ROOT = Path('/home/z/my-project/agahi-live')

HEX_MAP = {
    '0d9488': '4b4dbe',
    '0f766e': '3e4095',
    '115e59': '2c2e72',
    '134e4a': '23255c',
    '14b8a6': '5557b8',
    '2dd4bf': '7577cf',
    '5eead4': 'b9bbe8',
    '99f6e4': 'c6c7ee',
    'ccfbf1': 'e0e1f6',
    'f0fdfa': 'f0f1fa',
    'e6f4f1': 'ececf8',
    'f97316': 'c00c1a',
    'ea580c': '9e0a15',
}

RGBA_MAP = [
    (re.compile(r'rgba\(\s*13\s*,\s*148\s*,\s*136\s*,'), 'rgba(62,64,149,'),
    (re.compile(r'rgba\(\s*20\s*,\s*184\s*,\s*166\s*,'), 'rgba(85,87,184,'),
    (re.compile(r'rgba\(\s*15\s*,\s*118\s*,\s*110\s*,'), 'rgba(62,64,149,'),
    (re.compile(r'rgba\(\s*249\s*,\s*115\s*,\s*22\s*,'), 'rgba(192,12,26,'),
]

targets = [ROOT / 'public/assets/app.css']
targets += list((ROOT / 'resources/css').rglob('*.css'))
targets += list((ROOT / 'resources/views').rglob('*.blade.php'))

hex_re = re.compile('|'.join(HEX_MAP.keys()), re.IGNORECASE)

changed = 0
for path in targets:
    if not path.is_file():
        continue
    text = path.read_text(encoding='utf-8')
    original = text
    text = hex_re.sub(lambda m: HEX_MAP[m.group(0).lower()], text)
    for pattern, repl in RGBA_MAP:
        text = pattern.sub(repl, text)
    if text != original:
        path.write_text(text, encoding='utf-8')
        changed += 1
        print(f'updated: {path.relative_to(ROOT)}')

print(f'\n{changed} files updated')
