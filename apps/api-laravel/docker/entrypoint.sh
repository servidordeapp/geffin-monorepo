#!/bin/bash
set -e

WORKDIR=/var/www/html

if [ ! -f "$WORKDIR/artisan" ]; then
    echo "[GFN] Bootstrapping Laravel..."

    TMPDIR=$(mktemp -d)
    trap "rm -rf $TMPDIR" EXIT

    composer create-project laravel/laravel "$TMPDIR" --prefer-dist --no-interaction

    # Move files to workdir without overwriting existing files (e.g. docker/)
    find "$TMPDIR" -maxdepth 1 -mindepth 1 | while read -r item; do
        name=$(basename "$item")
        if [ ! -e "$WORKDIR/$name" ]; then
            mv "$item" "$WORKDIR/$name"
        fi
    done

    cd "$WORKDIR"
    php artisan key:generate
    echo "[GFN] Laravel bootstrapped successfully."
fi

chown -R www-data:www-data \
    "$WORKDIR/storage" \
    "$WORKDIR/bootstrap/cache" \
    2>/dev/null || true

exec "$@"
