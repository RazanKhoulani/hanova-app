#!/usr/bin/env sh

set -eu

php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force
php artisan config:cache
php artisan event:cache
php artisan view:cache
