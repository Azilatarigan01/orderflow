#!/bin/sh
set -e

# Ensure permissions on writable directories
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/private/pr_attachments \
         /var/www/html/storage/app/private/quotation_attachments \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# If .env does not exist, create from .env.example
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Create storage symlink if not already created
if [ ! -L /var/www/html/public/storage ]; then
    echo "Creating storage symlink..."
    php artisan storage:link || true
fi

# Ensure SQLite database file exists if DB_CONNECTION is sqlite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite || true
fi

# Auto migrate and seed if in cloud deployment
if [ -n "$PORT" ] || [ "$APP_ENV" = "production" ]; then
    echo "Running migrations and seeds..."
    php artisan migrate --seed --force || true
fi

# If PORT is set (such as on Render or Cloud PaaS), start web server
if [ -n "$PORT" ]; then
    echo "Starting web server on port $PORT..."
    exec php artisan serve --host=0.0.0.0 --port="$PORT"
fi

# Execute main container command (default: php-fpm for docker-compose)
exec "$@"
