#!/bin/sh
set -e

if [ "${FIX_PERMISSIONS:-false}" = "true" ]; then
    USER_ID=${UID:-1000}
    GROUP_ID=${GID:-1000}

    echo "Fixing file permissions with UID=${USER_ID} and GID=${GROUP_ID}..."
    chown -R ${USER_ID}:${GROUP_ID} /var/www || echo "Some files could not be changed"
fi

if [ "${CLEAR_LARAVEL_CACHE:-false}" = "true" ]; then
    echo "Clearing Laravel caches..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
fi

# Run the default command (e.g., php-fpm or bash)
exec "$@"
