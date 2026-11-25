<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ServiceArea;
use App\Models\User;
use App\Services\TripayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripayWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected CustomerAddress $address;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'phone' => '083186109775',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $area = ServiceArea::create([
            'name' => 'Kecamatan Coblong',
            'delivery_fee' => 5000,
            'estimated_delivery_time' => 30,
            'is_active' => true,
        ]);

        $this->address = CustomerAddress::create([
            'user_id' => $this->customer->id,
            'service_area_id' => $area->id,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'address_line' => 'Jl. Dago Asri No. 12',
            'is_primary' => true,
        ]);

        $this->product = Product::create([
            'name' => 'Air Galon Aqua 19L',
            'slug' => 'air-galon-aqua-19l',
            'price' => 20000,
            'cost_price' => 16000,
            'stock_quantity' => 50,
            'tracks_inventory' => true,
            'is_returnable_gallon' => true,
            'deposit_price' => 25000,
            'is_active' => true,
        ]);
    }

    public function test_tripay_convenience_fee_calculation(): void
    {
        BusinessSetting::set('tripay_pass_fee_to_customer', '1');

        $base = 45000;
        $fee = TripayService::calculateServiceFee($base);

        // 45000 / 0.993 = 45317.22 -> gross = 45318 -> fee = 318
        $this->assertEquals(318, $fee);
        $gross = $base + $fee;

        // When 0.7% is deducted from gross:
        $deduction = $gross * 0.007;
        $net = $gross - $deduction;

        // Net must be >= base (store is 100% net intact!)
        $this->assertGreaterThanOrEqual($base, $net);
    }

    public function test_tripay_webhook_rejects_invalid_event(): void
    {
        $response = $this->postJson('/api/webhooks/tripay-notification', [
            'status' => 'PAID',
        ], [
            'X-Callback-Event' => 'invalid_event',
        ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    public function test_tripay_webhook_validates_signature_when_configured(): void
    {
        BusinessSetting::set('tripay_api_key', 'DEV-TESTKEY');
        BusinessSetting::set('tripay_private_key', 'MY_SECRET_KEY');
        BusinessSetting::set('tripay_merchant_code', 'T0001');

        $order = Order::create([
            'order_number' => 'ORD-TRIPAY-001',
            'user_id' => $this->customer->id,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'delivery_address_text' => 'Jl. Dago Asri No. 12',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'qris',
            'tripay_reference' => 'DEV-T123456789',
            'subtotal' => 40000,
            'delivery_fee' => 5000,
            'service_fee' => 318,
            'total_amount' => 45318,
        ]);

        $payload = [
            'reference' => 'DEV-T123456789',
            'merchant_ref' => 'ORD-TRIPAY-001',
            'payment_method' => 'QRIS',
            'status' => 'PAID',
            'total_amount' => 45318,
        ];
        $rawJson = json_encode($payload);

        // Invalid signature
        $responseBad = $this->call(
            'POST',
            '/api/webhooks/tripay-notification',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
                'HTTP_X_CALLBACK_SIGNATURE' => 'invalid_signature_hash',
            ],
            $rawJson
        );
        $responseBad->assertStatus(403);

        // Valid signature
        $validSignature = hash_hmac('sha256', $rawJson, 'MY_SECRET_KEY');
        $responseGood = $this->call(
            'POST',
            '/api/webhooks/tripay-notification',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
                'HTTP_X_CALLBACK_SIGNATURE' => $validSignature,
            ],
            $rawJson
        );
        $responseGood->assertStatus(200)
            ->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('confirmed', $order->status);
    }

    public function test_tripay_webhook_marks_order_as_paid_and_creates_payment(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-TRIPAY-002',
            'user_id' => $this->customer->id,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'delivery_address_text' => 'Jl. Dago Asri No. 12',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'qris',
            'tripay_reference' => 'DEV-T999888777',
            'subtotal' => 20000,
            'delivery_fee' => 5000,
            'service_fee' => 176,
            'total_amount' => 25176,
        ]);

        $response = $this->postJson('/api/webhooks/tripay-notification', [
            'reference' => 'DEV-T999888777',
            'merchant_ref' => 'ORD-TRIPAY-002',
            'payment_method' => 'QRIS',
            'status' => 'PAID',
            'total_amount' => 25176,
        ], [
            'X-Callback-Event' => 'payment_status',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('confirmed', $order->status);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'qris',
            'status' => 'paid',
            'amount' => 25176,
        ]);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_tripay_simulate_endpoint(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-SIM-001',
            'user_id' => $this->customer->id,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'delivery_address_text' => 'Jl. Dago Asri No. 12',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'qris',
            'subtotal' => 20000,
            'delivery_fee' => 5000,
            'service_fee' => 176,
            'total_amount' => 25176,
        ]);

        $response = $this->postJson("/api/webhooks/tripay-simulate/{$order->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'payment_status' => 'paid']);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('confirmed', $order->status);
    }
}
