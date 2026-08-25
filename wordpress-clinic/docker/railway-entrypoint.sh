#!/bin/sh
set -eu

# Railway injects PORT at runtime while the Apache image defaults to port 80.
if [ -n "${PORT:-}" ] && [ "${PORT}" != "80" ]; then
    sed -ri "s!^Listen [0-9]+!Listen ${PORT}!" /etc/apache2/ports.conf
    sed -ri "s!<VirtualHost \*:[0-9]+>!<VirtualHost *:${PORT}>!" /etc/apache2/sites-available/000-default.conf
fi

exec docker-entrypoint.sh "$@"
