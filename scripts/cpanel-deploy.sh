#!/usr/bin/env bash

set -Eeuo pipefail

APP_DIR="/home/asianhea/asian-health-connect"
PUBLIC_DIR="/home/asianhea/public_html"
PHP_BIN="/usr/local/bin/php"
COMPOSER_BIN="/usr/local/bin/composer"
BUILD_DIR="$APP_DIR/.deploy/build"

cd "$APP_DIR"

if [[ ! -f .env ]]; then
    echo "Deployment stopped: $APP_DIR/.env is missing." >&2
    exit 1
fi

if [[ ! -f "$BUILD_DIR/manifest.json" ]]; then
    BUILD_DIR="$APP_DIR/public/build"
fi

if [[ ! -f "$BUILD_DIR/manifest.json" ]]; then
    echo "Deployment stopped: the production Vite manifest is missing." >&2
    exit 1
fi

"$COMPOSER_BIN" install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

for directory in css images js; do
    if [[ -d "public/$directory" ]]; then
        mkdir -p "$PUBLIC_DIR/$directory"
        cp -a "public/$directory/." "$PUBLIC_DIR/$directory/"
    fi
done

source_build="$(readlink -f "$BUILD_DIR")"
target_build="$(readlink -f "$PUBLIC_DIR/build")"

if [[ "$source_build" != "$target_build" ]]; then
    mkdir -p "$PUBLIC_DIR/build"
    cp -a "$BUILD_DIR/." "$PUBLIC_DIR/build/"
fi

chmod -R ug+rwX storage bootstrap/cache

"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan migrate --force
"$PHP_BIN" artisan db:seed --class=BrandedHeroSlidesSeeder --force
"$PHP_BIN" artisan db:seed --class=TreatmentCatalogSeeder --force
"$PHP_BIN" artisan db:seed --class=BanglaContentSeeder --force
"$PHP_BIN" artisan doctors:import database/rosters/apollo-doctors-2026-09-17.csv --commit
"$PHP_BIN" artisan optimize
"$PHP_BIN" artisan queue:restart

"$PHP_BIN" -l "$PUBLIC_DIR/index.php"
test -f "$PUBLIC_DIR/build/manifest.json"

echo "Deployment completed successfully."
