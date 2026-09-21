#!/usr/bin/env bash
# Hostinger Git build hook (set in hPanel Git → build command, if available).
# Composer needs proc_open, which Hostinger disables by default on new accounts.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ -f vendor/autoload.php ]]; then
    echo "vendor/autoload.php present — skipping composer install."
    exit 0
fi

echo "ERROR: vendor/ is missing. Either:"
echo "  1) Enable proc_open: hPanel → PHP Configuration → disableFunctions (remove proc_open), redeploy; or"
echo "  2) Deploy via GitHub Actions (includes vendor), or run scripts/hostinger-setup.sh over SSH."
exit 1
