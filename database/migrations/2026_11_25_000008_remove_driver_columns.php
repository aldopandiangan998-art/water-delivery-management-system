<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF;');

        DB::statement('
            CREATE TABLE deliveries_temp (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                delivery_number VARCHAR NOT NULL UNIQUE,
                order_id INTEGER NOT NULL,
                status VARCHAR NOT NULL DEFAULT "not_assigned",
                started_at DATETIME,
                arrived_at DATETIME,
                completed_at DATETIME,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE
            )
        ');

        DB::statement('
            INSERT INTO deliveries_temp (id, delivery_number, order_id, status, started_at, arrived_at, completed_at, created_at, updated_at)
            SELECT id, delivery_number, order_id, status, started_at, arrived_at, completed_at, created_at, updated_at FROM deliveries
        ');

        DB::statement('DROP TABLE deliveries;');
        DB::statement('ALTER TABLE deliveries_temp RENAME TO deliveries;');

        DB::statement('
            CREATE TABLE delivery_locations_temp (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                delivery_id INTEGER NOT NULL,
                latitude NUMERIC(10, 7) NOT NULL,
                longitude NUMERIC(10, 7) NOT NULL,
                accuracy NUMERIC(8, 2),
                heading NUMERIC(8, 2),
                speed NUMERIC(8, 2),
                recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                created_at DATETIME,
                updated_at DATETIME,
                FOREIGN KEY(delivery_id) REFERENCES deliveries(id) ON DELETE CASCADE
            )
        ');

        DB::statement('
            INSERT INTO delivery_locations_temp (id, delivery_id, latitude, longitude, accuracy, heading, speed, recorded_at, created_at, updated_at)
            SELECT id, delivery_id, latitude, longitude, accuracy, heading, speed, recorded_at, created_at, updated_at FROM delivery_locations
        ');

        DB::statement('DROP TABLE delivery_locations;');
        DB::statement('ALTER TABLE delivery_locations_temp RENAME TO delivery_locations;');
        DB::statement('CREATE INDEX delivery_locations_delivery_id_recorded_at_index ON delivery_locations(delivery_id, recorded_at);');

        DB::statement('PRAGMA foreign_keys = ON;');
    }

    public function down(): void
    {
    }
};
