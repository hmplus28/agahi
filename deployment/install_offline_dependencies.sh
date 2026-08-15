#!/usr/bin/env bash
# Install only from an already prepared local wheelhouse; no network access is attempted.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WHEELHOUSE_DIR="${1:?Provide the private wheelhouse directory as the first argument.}"
VENV_PYTHON="${VENV_PYTHON:-python3}"
INCLUDE_INTEGRATIONS="${INCLUDE_INTEGRATIONS:-false}"

"${VENV_PYTHON}" -m pip install --no-index --find-links "${WHEELHOUSE_DIR}" -r "${ROOT_DIR}/requirements/base.txt"
if [[ "${INCLUDE_INTEGRATIONS}" == "true" ]]; then
  "${VENV_PYTHON}" -m pip install --no-index --find-links "${WHEELHOUSE_DIR}" -r "${ROOT_DIR}/requirements/integrations.txt"
fi

printf 'Offline dependency installation completed.\n'
