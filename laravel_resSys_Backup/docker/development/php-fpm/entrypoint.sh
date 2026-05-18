#!/bin/sh
set -e

# Check if $UID and $GID are set, else fallback to default (1000:1000)
USER_ID=${UID:-1000}
GROUP_ID=${GID:-1000}

# Fix file ownership and permissions using the passed UID and GID
if [ "$(id -u)" -eq 0 ]; then
	echo "Fixing file permissions with UID=${USER_ID} and GID=${GROUP_ID}..."
	chown -R ${USER_ID}:${GROUP_ID} /var/www || echo "Some files could not be changed"
else
	echo "Skipping chown because container is running as non-root user ($(id -u):$(id -g))."
fi

# Clear configurations to avoid caching issues in development
if [ -f /var/www/artisan ] && [ -f /var/www/vendor/autoload.php ]; then
	echo "Clearing configurations..."
	rm -f /var/www/bootstrap/cache/*.php || true
	php /var/www/artisan config:clear || true
	php /var/www/artisan route:clear || true
	php /var/www/artisan view:clear || true
else
	echo "Skipping artisan cache clear: Laravel files are not fully available yet."
fi

# Run the default command (e.g., php-fpm or bash)
exec "$@"
