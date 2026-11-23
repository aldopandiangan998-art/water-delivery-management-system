<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Delivery status: not_assigned, assigned, in_transit, arrived, delivered, failed, cancelled
            $table->string('status')->default('not_assigned');
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('driver_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('delivery_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->decimal('heading', 8, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['delivery_id', 'recorded_at']);
        });

        Schema::create('delivery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('caption')->nullable();
            $table->timestamp('taken_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_photos');
        Schema::dropIfExists('delivery_locations');
        Schema::dropIfExists('delivery_status_histories');
        Schema::dropIfExists('deliveries');
    }
};
