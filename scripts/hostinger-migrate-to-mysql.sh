#!/usr/bin/env bash
# Move existing site content from database/database.sqlite into MySQL (keeps blogs/CMS).
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

if [[ ! -f .env ]]; then
    echo "Create .env with MySQL credentials first (see docs/HOSTINGER-DATABASE.md)." >&2
    exit 1
fi

if ! grep -qE '^DB_CONNECTION=mysql' .env; then
    echo "Set DB_CONNECTION=mysql in .env before migrating." >&2
    exit 1
fi

SQLITE="${1:-database/database.sqlite}"

if [[ ! -f "$SQLITE" ]]; then
    echo "SQLite file not found: $SQLITE" >&2
    echo "Your live content may already be in MySQL, or the file is elsewhere." >&2
    exit 1
fi

cp -a "$SQLITE" "${SQLITE}.bak-$(date +%Y%m%d%H%M%S)"
echo "Backup saved beside $SQLITE"

echo "Preview:"
"$PHP_BIN" artisan site:import-sqlite --path="$SQLITE" --dry-run --no-interaction

read -r -p "Import into MySQL now? [y/N] " ans
if [[ "${ans,,}" != "y" ]]; then
    echo "Cancelled."
    exit 0
fi

"$PHP_BIN" artisan site:import-sqlite --path="$SQLITE" --no-interaction

touch storage/framework/installed
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache

echo "Done. Check the site and admin, then you can remove ${SQLITE} if everything looks correct."
