<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('min_order_amount', 12, 2)->default(0);
            $table->string('estimated_delivery_time')->nullable()->default('30-60 menit');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_areas');
    }
};
