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
    echo "See docs/HOSTINGER-DATABASE.md"
    exit 1
fi

if grep -qE '^DB_(DATABASE|USERNAME|PASSWORD)=your_' .env 2>/dev/null \
    || grep -qE '^DB_(DATABASE|USERNAME|PASSWORD)=$' .env 2>/dev/null; then
    echo "Edit .env: replace placeholder DB_DATABASE, DB_USERNAME, DB_PASSWORD (hPanel → MySQL Databases)." >&2
    echo "Guide: docs/HOSTINGER-DATABASE.md" >&2
    exit 1
fi

if ! grep -qE '^DB_CONNECTION=mysql' .env; then
    echo "Set DB_CONNECTION=mysql in .env — see docs/HOSTINGER-DATABASE.md" >&2
    exit 1
fi

if [[ ! -f composer.json && -f composer.json.dist ]]; then
    cp composer.json.dist composer.json
    cp composer.lock.dist composer.lock
fi

if [[ -f vendor/autoload.php ]]; then
    echo "Using committed vendor/ — skipping composer install (avoids proc_open on Hostinger)."
elif [[ -f composer.json ]]; then
    if [[ ! -f "$ROOT/composer.phar" ]]; then
        "$PHP_BIN" -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        "$PHP_BIN" composer-setup.php --install-dir="$ROOT" --filename=composer.phar --quiet
        rm -f composer-setup.php
    fi

    "$PHP_BIN" "$ROOT/composer.phar" install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs
else
    echo "Missing vendor/ and composer.json.dist — cannot install PHP dependencies." >&2
    exit 1
fi

if ! grep -qE '^APP_KEY=base64:' .env; then
    "$PHP_BIN" artisan key:generate --force
fi

echo "Testing MySQL connection…"
if ! "$PHP_BIN" artisan migrate:status --no-interaction >/dev/null 2>&1; then
    echo "Cannot connect to MySQL. Check DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env (hPanel → Databases)." >&2
    echo "See docs/HOSTINGER-DATABASE.md" >&2
    exit 1
fi

"$PHP_BIN" artisan migrate --force

if [[ ! -f storage/framework/installed ]]; then
    "$PHP_BIN" artisan db:seed --force
else
    echo "Skipping db:seed — site already installed (keeps your CMS posts, pages, and settings)."
fi
"$PHP_BIN" artisan storage:link --force
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

chmod -R ug+rwx storage bootstrap/cache || true
touch storage/framework/installed || true

echo "Hostinger setup finished. Visit your domain and log in at /admin/login"
