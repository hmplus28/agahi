#!/usr/bin/env bash
# Run once while internet access is available, then store the resulting directory privately.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WHEELHOUSE_DIR="${1:-${ROOT_DIR}/wheelhouse}"
INCLUDE_INTEGRATIONS="${INCLUDE_INTEGRATIONS:-false}"

mkdir -p "${WHEELHOUSE_DIR}"
python3 -m pip download --dest "${WHEELHOUSE_DIR}" -r "${ROOT_DIR}/requirements/base.txt"

if [[ "${INCLUDE_INTEGRATIONS}" == "true" ]]; then
  python3 -m pip download --dest "${WHEELHOUSE_DIR}" -r "${ROOT_DIR}/requirements/integrations.txt"
fi

printf 'Offline wheelhouse prepared at: %s\n' "${WHEELHOUSE_DIR}"
printf 'Install with: python3 -m pip install --no-index --find-links "%s" -r requirements/base.txt\n' "${WHEELHOUSE_DIR}"
