<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DanaWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected ServiceArea $serviceArea;
    protected CustomerAddress $address;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@test.com',
            'phone' => '08123456789',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $this->admin = User::create([
            'name' => 'Admin Nova Jaya',
            'email' => 'admin@novajaya.com',
            'phone' => '083186109775',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $this->serviceArea = ServiceArea::create([
            'name' => 'Ngagelrejo',
            'delivery_fee' => 3000,
            'min_order_amount' => 10000,
            'is_active' => true,
        ]);

        $this->address = CustomerAddress::create([
            'user_id' => $this->customer->id,
            'label' => 'Rumah',
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '08123456789',
            'address_line' => 'Jl. Bratang Gede No. 10',
            'service_area_id' => $this->serviceArea->id,
            'is_default' => true,
        ]);

        $cat = ProductCategory::create([
            'name' => 'Air Galon',
            'slug' => 'air-galon',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $cat->id,
            'name' => 'Air Isi Ulang Super',
            'slug' => 'air-isi-ulang-super',
            'price' => 7000,
            'cost_price' => 2000,
            'stock_quantity' => 100,
            'unit' => 'galon',
            'is_returnable_gallon' => true,
            'deposit_price' => 35000,
            'tracks_inventory' => true,
            'is_active' => true,
        ]);

        // Default secret
        BusinessSetting::set('dana_webhook_secret', 'test-dana-secret-key-123');
        BusinessSetting::set('company_whatsapp', '6283186109775');
    }

    public function test_webhook_rejects_unauthorized_request_without_secret(): void
    {
        $response = $this->postJson('/api/webhooks/dana-notification', [
            'amount' => 20000,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Unauthorized: Invalid or missing webhook secret key.',
        ]);
    }

    public function test_webhook_rejects_invalid_secret_key(): void
    {
        $response = $this->withHeaders([
            'X-Webhook-Secret' => 'wrong-secret-key',
        ])->postJson('/api/webhooks/dana-notification', [
            'amount' => 20000,
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_rejects_invalid_or_missing_amount(): void
    {
        $response = $this->withHeaders([
            'X-Webhook-Secret' => 'test-dana-secret-key-123',
        ])->postJson('/api/webhooks/dana-notification', [
            'message' => 'Halo ini bukan notifikasi dana masuk',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_webhook_matches_and_marks_unpaid_qris_order_as_paid_successfully(): void
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => true, 'id' => 'msg-123'], 200),
        ]);

        // Create unpaid QRIS order for Rp 24.000
        $order = Order::create([
            'order_number' => 'ORD-TEST-QRIS-01',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => $this->address->recipient_name,
            'recipient_phone' => $this->address->recipient_phone,
            'delivery_address_text' => $this->address->address_line,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'qris',
            'subtotal' => 21000,
            'delivery_fee' => 3000,
            'discount' => 0,
            'total_amount' => 24000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'unit' => $this->product->unit,
            'price' => 7000,
            'cost_price' => 2000,
            'quantity' => 3,
            'subtotal' => 21000,
        ]);

        // Send DANA notification via webhook with formatted notification text
        $response = $this->withHeaders([
            'X-Webhook-Secret' => 'test-dana-secret-key-123',
        ])->postJson('/api/webhooks/dana-notification', [
            'text' => 'Kamu menerima uang Rp 24.000 dari SITI AMINAH',
            'amount' => 24000,
            'sender' => 'SITI AMINAH',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'matched' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => 'ORD-TEST-QRIS-01',
                'payment_status' => 'paid',
            ],
        ]);

        // Verify order is marked as paid
        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);

        // Verify Payment record was generated
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'qris',
            'amount' => 24000,
            'status' => 'paid',
        ]);

        // Verify OrderStatusHistory was generated
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_webhook_parses_amount_from_notification_text_when_amount_field_is_empty(): void
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => true], 200),
        ]);

        // Create unpaid QRIS order for Rp 17.500
        $order = Order::create([
            'order_number' => 'ORD-TEST-REGEX-02',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => $this->address->recipient_name,
            'recipient_phone' => $this->address->recipient_phone,
            'delivery_address_text' => $this->address->address_line,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'qris',
            'subtotal' => 17500,
            'delivery_fee' => 0,
            'discount' => 0,
            'total_amount' => 17500,
        ]);

        // Payload with text only (amount is empty)
        $response = $this->withHeaders([
            'X-Webhook-Secret' => 'test-dana-secret-key-123',
        ])->postJson('/api/webhooks/dana-notification', [
            'text' => 'Berhasil menerima saldo Rp 17.500 dari AHMAD FAUZI',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'matched' => true,
            'order' => [
                'id' => $order->id,
                'payment_status' => 'paid',
            ],
        ]);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
    }

    public function test_webhook_handles_unmatched_amount_gracefully(): void
    {
        $response = $this->withHeaders([
            'X-Webhook-Secret' => 'test-dana-secret-key-123',
        ])->postJson('/api/webhooks/dana-notification', [
            'amount' => 999999,
            'text' => 'Kamu menerima uang Rp 999.999 dari SIAPA SAJA',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'matched' => false,
        ]);
    }

    public function test_customer_can_poll_order_status_check(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-POLL-01',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => $this->address->recipient_name,
            'recipient_phone' => $this->address->recipient_phone,
            'delivery_address_text' => $this->address->address_line,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'qris',
            'subtotal' => 10000,
            'delivery_fee' => 0,
            'discount' => 0,
            'total_amount' => 10000,
        ]);

        // Customer can check their own order status
        $response = $this->actingAs($this->customer)->getJson(route('customer.orders.status-check', $order));
        $response->assertStatus(200);
        $response->assertJson([
            'order_id' => $order->id,
            'payment_status' => 'unpaid',
            'is_paid' => false,
        ]);

        // Other customer cannot check this order
        $otherCustomer = User::create([
            'name' => 'Pelanggan Lain',
            'email' => 'lain@test.com',
            'phone' => '08999999999',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $forbiddenResponse = $this->actingAs($otherCustomer)->getJson(route('customer.orders.status-check', $order));
        $forbiddenResponse->assertStatus(403);
    }

    public function test_admin_can_view_and_update_integration_settings(): void
    {
        // Admin views integrations page
        $response = $this->actingAs($this->admin)->get(route('admin.settings.integrations'));
        $response->assertStatus(200);
        $response->assertSee('URL Webhook DANA');
        $response->assertSee('Panduan Setup MacroDroid');

        // Admin updates settings
        $updateResponse = $this->actingAs($this->admin)->post(route('admin.settings.integrations.update'), [
            'dana_webhook_secret' => 'new-secret-key-999',
            'company_whatsapp' => '628111222333',
            'fonnte_api_token' => 'fonnte-sample-token-abc',
        ]);

        $updateResponse->assertRedirect();
        $this->assertEquals('new-secret-key-999', BusinessSetting::get('dana_webhook_secret'));
        $this->assertEquals('628111222333', BusinessSetting::get('company_whatsapp'));
        $this->assertEquals('fonnte-sample-token-abc', BusinessSetting::get('fonnte_api_token'));
    }
}
