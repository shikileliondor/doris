#!/bin/sh
set -e

cd /var/www/html

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ ! -f "$DB_DATABASE" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

# Cache config, routes, events and views with the container's runtime environment.
php artisan optimize --no-interaction

exec "$@"
