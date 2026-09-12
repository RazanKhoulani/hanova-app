#!/usr/bin/env sh

set -eu

# The storage volume is available at runtime, after the image is built.
mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
sh ./railway/init-app.sh

exec docker-php-entrypoint --config /Caddyfile --adapter caddyfile
