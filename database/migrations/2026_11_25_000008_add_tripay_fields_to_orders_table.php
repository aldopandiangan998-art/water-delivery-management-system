<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('service_fee', 12, 2)->default(0)->after('delivery_fee');
            $table->string('tripay_reference')->nullable()->after('payment_method');
            $table->string('tripay_merchant_ref')->nullable()->after('tripay_reference');
            $table->text('qris_qr_url')->nullable()->after('tripay_merchant_ref');
            $table->text('qris_raw_qr_string')->nullable()->after('qris_qr_url');
            $table->text('qris_checkout_url')->nullable()->after('qris_raw_qr_string');
            $table->timestamp('qris_expired_at')->nullable()->after('qris_checkout_url');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'service_fee',
                'tripay_reference',
                'tripay_merchant_ref',
                'qris_qr_url',
                'qris_raw_qr_string',
                'qris_checkout_url',
                'qris_expired_at',
            ]);
        });
    }
};
