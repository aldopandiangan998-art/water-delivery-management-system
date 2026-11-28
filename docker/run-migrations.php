<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run migrations safely
echo "Menjalankan database migrations...\n";
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo \Illuminate\Support\Facades\Artisan::output();

// Seed initial data if users table is empty
if (\App\Models\User::count() === 0) {
    echo "Database kosong. Menjalankan seeder awal...\n";
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    echo \Illuminate\Support\Facades\Artisan::output();
} else {
    echo "Database sudah memiliki data pengguna (" . \App\Models\User::count() . " user). Melewati seeder.\n";
}
