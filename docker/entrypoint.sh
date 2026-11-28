#!/bin/sh
set -e

# Pastikan file SQLite tersedia jika menggunakan koneksi sqlite
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "Membuat berkas database.sqlite baru..."
        touch /var/www/html/database/database.sqlite
        chown -R www-data:www-data /var/www/html/database
        chmod -R 775 /var/www/html/database
    fi
fi

# Jalankan migrasi dan inisialisasi seeder
php /var/www/html/docker/run-migrations.php

# Buat symlink storage publik
php artisan storage:link || true

# Optimasi cache untuk production
if [ "$APP_ENV" = "production" ]; then
    echo "Mengoptimasi cache Laravel untuk production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "AquaFlow Delivery System siap! Menjalankan web server pada port $PORT..."
exec "$@"
