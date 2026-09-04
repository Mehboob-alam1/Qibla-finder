#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ ! -f .env ]]; then
    echo "Missing .env — copy .env.hostinger.example to .env and fill in Hostinger MySQL + APP_URL."
    exit 1
fi

COMPOSER_BIN="${COMPOSER_BIN:-composer}"
if ! command -v "$COMPOSER_BIN" >/dev/null 2>&1; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir="$ROOT" --filename=composer.phar
    rm -f composer-setup.php
    COMPOSER_BIN="php $ROOT/composer.phar"
fi

$COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction

if ! grep -qE '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

php artisan migrate --force --seed
php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R ug+rwx storage bootstrap/cache || true

echo "Hostinger setup finished. Visit your domain and log in at /admin/login"
