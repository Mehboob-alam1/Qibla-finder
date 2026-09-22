#!/usr/bin/env bash
# Hostinger Git: if custom build command can be set to `bash build.sh`, use this.
# Dependencies are committed in vendor/ — Composer must not run on Hostinger (proc_open).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

if [[ ! -f composer.json && -f composer.json.dist ]]; then
    cp composer.json.dist composer.json
fi

if [[ ! -f composer.lock && -f composer.lock.dist ]]; then
    cp composer.lock.dist composer.lock
fi

if [[ ! -f vendor/autoload.php ]]; then
    echo "ERROR: vendor/autoload.php missing from repository."
    exit 1
fi

if [[ -f public/build/manifest.json ]]; then
    echo "Build OK — vendored PHP deps + committed public/build."
    exit 0
fi

if command -v npm >/dev/null 2>&1 && [[ -f package.json ]]; then
    npm run build
    exit 0
fi

echo "ERROR: public/build/manifest.json missing and npm is not available."
exit 1
