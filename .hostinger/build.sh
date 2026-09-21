#!/usr/bin/env bash
# If Hostinger Git allows a custom build command, point it here.
# Production dependencies are committed in vendor/ so Composer does not need to run on Hostinger.
set -euo pipefail
cd "$(dirname "$0")/.."
if [[ -f vendor/autoload.php ]]; then
    if [[ ! -f composer.json && -f composer.json.dist ]]; then
        cp composer.json.dist composer.json
    fi
    echo "Using vendored dependencies from the repository."
    exit 0
fi
echo "vendor/autoload.php missing — enable proc_open and redeploy, or pull a commit that includes vendor/."
exit 1
