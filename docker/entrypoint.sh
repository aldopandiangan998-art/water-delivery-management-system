#!/bin/sh
set -e

# Default environment variables jika tidak diatur di cloud
if [ -z "$APP_KEY" ]; then
    export APP_KEY="base64:XU1qTh3kxJ5raLTDzjg5RqKnBuTnesdlMOhi2VM8oOQ="
fi
if [ -z "$APP_ENV" ]; then
    export APP_ENV="production"
fi
if [ -z "$DB_CONNECTION" ]; then
    export DB_CONNECTION="sqlite"
fi

# Pastikan file SQLite tersedia jika menggunakan koneksi sqlite
if [ "$DB_CONNECTION" = "sqlite" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        echo "Membuat berkas database.sqlite baru..."
        touch /var/www/html/database/database.sqlite
    fi
    chmod -R 777 /var/www/html/database
fi

# Pastikan permission folder storage dan cache selalu writable
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

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
