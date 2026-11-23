<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('delivery_address_text');
            $table->string('delivery_benchmark')->nullable();
            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();
            
            // Order status: pending, confirmed, processing, ready_for_delivery, completed, cancelled, rejected
            $table->string('status')->default('pending');
            // Payment status: unpaid, partially_paid, paid, refunded
            $table->string('payment_status')->default('unpaid');
            // Payment method: cod, bank_transfer
            $table->string('payment_method')->default('cod');
            
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            
            $table->integer('empty_gallons_returned')->default(0);
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name'); // snapshot
            $table->string('unit')->default('galon'); // snapshot
            $table->decimal('price', 12, 2); // snapshot
            $table->decimal('cost_price', 12, 2)->default(0); // snapshot
            $table->integer('quantity');
            $table->boolean('is_exchange')->default(false); // customer supplies empty bottle for refill
            $table->decimal('subtotal', 12, 2);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
