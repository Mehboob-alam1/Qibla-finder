#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

pick_php() {
    local candidate
    for candidate in \
        /opt/alt/php85/usr/bin/php \
        /opt/alt/php84/usr/bin/php \
        /opt/alt/php83/usr/bin/php \
        php8.5 php8.4 php8.3 \
        php
    do
        if [[ -x "$candidate" ]] || command -v "$candidate" >/dev/null 2>&1; then
            if "$candidate" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' 2>/dev/null; then
                echo "$candidate"
                return 0
            fi
        fi
    done
    echo "Need PHP 8.3 or newer. In hPanel set PHP Configuration to 8.3+, then rerun:" >&2
    echo "  /opt/alt/php83/usr/bin/php -v" >&2
    exit 1
}

PHP_BIN="${PHP_BIN:-$(pick_php)}"

if [[ ! -f .env ]]; then
    echo "Missing .env — copy .env.hostinger.example to .env and fill in Hostinger MySQL + APP_URL."
    exit 1
fi

if [[ ! -f "$ROOT/composer.phar" ]]; then
    "$PHP_BIN" -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    "$PHP_BIN" composer-setup.php --install-dir="$ROOT" --filename=composer.phar --quiet
    rm -f composer-setup.php
fi

"$PHP_BIN" "$ROOT/composer.phar" install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

if ! grep -qE '^APP_KEY=base64:' .env; then
    "$PHP_BIN" artisan key:generate --force
fi

"$PHP_BIN" artisan migrate --force --seed
"$PHP_BIN" artisan storage:link --force
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

chmod -R ug+rwx storage bootstrap/cache || true
touch storage/framework/installed || true

echo "Hostinger setup finished. Visit your domain and log in at /admin/login"
