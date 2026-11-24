#!/bin/bash
set -e

# Dynamically bind Nginx to Render's assigned $PORT (defaults to 80)
PORT=${PORT:-80}
echo "Configuring Nginx to listen on port ${PORT}..."
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/http.d/default.conf

# Setup SQLite database if needed
DB_DIR="/var/www/html/database"
DB_FILE="${DB_DIR}/database.sqlite"

mkdir -p "$DB_DIR"
if [ ! -f "$DB_FILE" ]; then
    echo "Creating SQLite database file..."
    touch "$DB_FILE"
fi

# Ensure storage & database directories have correct permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache "$DB_DIR"
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache "$DB_DIR"
if [ -f "$DB_FILE" ]; then
    chmod 664 "$DB_FILE"
fi

# Ensure APP_KEY exists
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY not set. Generating temporary key..."
    php artisan key:generate --force
fi

# Run migrations and seed default data
echo "Running migrations and seeders..."
php artisan migrate --force --seed || php artisan migrate --force

# Optimize Laravel cache for production
echo "Caching configuration and routes..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting Supervisor (Nginx + PHP-FPM)..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
