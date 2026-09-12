#!/usr/bin/env sh

set -eu

php artisan config:clear
php artisan migrate --force
php artisan optimize:clear
php artisan hanova:bootstrap
php artisan storage:link --force
php artisan config:cache
php artisan event:cache
php artisan view:cache
