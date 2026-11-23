<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('customer_type')->default('household'); // household, retail_shop, office, restaurant
            $table->integer('gallon_deposit_balance')->default(0); // number of returnable gallons currently held
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->default('Rumah'); // Rumah, Kantor, Toko, etc.
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('address_line');
            $table->string('benchmark')->nullable(); // patokan lokasi
            $table->foreignId('service_area_id')->nullable()->constrained('service_areas')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customer_profiles');
    }
};
