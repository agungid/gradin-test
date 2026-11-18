#!/usr/bin/env bash
set -e

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ "$DB_CONNECTION" = "mysql" ] && [ -n "$DB_HOST" ]; then
    echo "Waiting for MySQL at $DB_HOST:${DB_PORT:-3306}..."
    until mysqladmin ping -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent; do
        sleep 2
    done
    echo "MySQL is ready."
fi

if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --force
fi

chown -R www-data:www-data storage bootstrap/cache

php artisan migrate --force

exec "$@"
