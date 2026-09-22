#!/usr/bin/env bash
# npm "build" script — Hostinger runs this on deploy; must not require Bunny CDN or full Node.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

MANIFEST="public/build/manifest.json"

if [[ "${FORCE_VITE_BUILD:-}" == "1" ]]; then
    exec npx vite build
fi

# GitHub Actions / CI: always compile so assets match the commit.
if [[ "${CI:-}" == "true" ]]; then
    exec npx vite build
fi

# Hostinger & local default: use committed public/build when present.
if [[ -f "$MANIFEST" ]]; then
    echo "Using committed public/build (skip Vite). Run npm run build:force after CSS/JS changes."
    exit 0
fi

exec npx vite build
