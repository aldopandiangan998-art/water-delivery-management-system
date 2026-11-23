<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method')->default('cod'); // cod, bank_transfer
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('unpaid'); // unpaid, paid, refunded
            $table->string('proof_image')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->string('category')->default('other'); // stock, fuel, vehicle_maintenance, salary, utilities, other
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payments');
    }
};
