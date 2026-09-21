#!/usr/bin/env bash
# Run on the server after code is updated (SSH or FTP). Does not run Composer.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

pick_php() {
    for candidate in \
        /opt/alt/php85/usr/bin/php \
        /opt/alt/php84/usr/bin/php \
        /opt/alt/php83/usr/bin/php \
        php8.3 php
    do
        if command -v "$candidate" >/dev/null 2>&1 \
            && "$candidate" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' 2>/dev/null; then
            echo "$candidate"
            return 0
        fi
    done
    echo "Need PHP 8.3+" >&2
    exit 1
}

PHP_BIN="${PHP_BIN:-$(pick_php)}"

if [[ ! -f vendor/autoload.php ]]; then
    echo "Missing vendor/. Deploy must include committed vendor/ or run composer locally." >&2
    exit 1
fi

if [[ ! -f .env ]]; then
    echo "Missing .env — copy .env.hostinger.example to .env first." >&2
    exit 1
fi

"$PHP_BIN" artisan migrate --force

if [[ ! -f storage/framework/installed ]]; then
    "$PHP_BIN" artisan db:seed --force
    touch storage/framework/installed
fi

"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "Post-deploy finished."
