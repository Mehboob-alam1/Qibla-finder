#!/usr/bin/env bash
# Emergency: find SQLite copies on the server and import into MySQL.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

pick_php() {
    for candidate in /opt/alt/php83/usr/bin/php /opt/alt/php84/usr/bin/php php8.3 php; do
        if command -v "$candidate" >/dev/null 2>&1; then
            echo "$candidate"
            return 0
        fi
    done
    exit 1
}

PHP_BIN="${PHP_BIN:-$(pick_php)}"

echo "=== Looking for SQLite database files (your old CMS may be here) ==="
mapfile -t FILES < <(find "$ROOT" "$HOME" -maxdepth 6 \( -name 'database.sqlite' -o -name 'database.sqlite.bak-*' \) 2>/dev/null | sort -u)

if [[ ${#FILES[@]} -eq 0 ]]; then
    echo "No SQLite files found under site or home directory."
    echo "Try hPanel → Backups → restore Files or Database from yesterday."
    exit 1
fi

for i in "${!FILES[@]}"; do
    echo "  [$i] ${FILES[$i]}"
done

echo ""
read -r -p "Which file number to import into MySQL? (or q to quit) " pick

if [[ "$pick" == "q" || "$pick" == "" ]]; then
    exit 0
fi

FILE="${FILES[$pick]:-}"
if [[ ! -f "$FILE" ]]; then
    echo "Invalid choice."
    exit 1
fi

echo "Dry run for: $FILE"
"$PHP_BIN" artisan site:import-sqlite --path="$FILE" --dry-run --no-interaction

read -r -p "Import this file into MySQL? [y/N] " ans
if [[ "${ans,,}" != "y" ]]; then
    exit 0
fi

"$PHP_BIN" artisan site:import-sqlite --path="$FILE" --no-interaction
"$PHP_BIN" artisan config:clear
echo "Import done. Check /guides and /admin — then php artisan config:cache"
