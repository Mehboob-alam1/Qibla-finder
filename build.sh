#!/usr/bin/env bash
# Hostinger Git: if custom build command can be set to `bash build.sh`, use this.
# Dependencies are committed in vendor/ — Composer must not run on Hostinger (proc_open).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

if [[ ! -f vendor/autoload.php ]]; then
    echo "ERROR: vendor/autoload.php missing from repository."
    exit 1
fi

if [[ ! -f composer.json && -f composer.json.dist ]]; then
    cp composer.json.dist composer.json
fi

echo "Build OK — using vendored dependencies (no composer install)."
