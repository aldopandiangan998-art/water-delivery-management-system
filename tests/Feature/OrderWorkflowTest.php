<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceArea;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected CustomerAddress $address;
    protected Product $product;
    protected ServiceArea $serviceArea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name' => 'Budi Pelanggan',
            'email' => 'budi@test.com',
            'phone' => '08123456789',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $this->serviceArea = ServiceArea::create([
            'name' => 'Sukajadi Barat',
            'delivery_fee' => 3000,
            'min_order_amount' => 10000,
            'is_active' => true,
        ]);

        $this->address = CustomerAddress::create([
            'user_id' => $this->customer->id,
            'label' => 'Rumah Utama',
            'recipient_name' => 'Budi Pelanggan',
            'recipient_phone' => '08123456789',
            'address_line' => 'Jl. Sukajadi No. 50',
            'service_area_id' => $this->serviceArea->id,
            'latitude' => -6.8850,
            'longitude' => 107.5960,
            'is_default' => true,
        ]);

        $cat = ProductCategory::create([
            'name' => 'Galon Refill',
            'slug' => 'galon-refill',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $cat->id,
            'name' => 'Air RO Murni Galon',
            'slug' => 'air-ro-murni-galon',
            'unit' => 'galon',
            'price' => 7000,
            'cost_price' => 2000,
            'is_returnable_gallon' => true,
            'deposit_price' => 35000,
            'tracks_inventory' => true,
            'stock_quantity' => 100,
            'is_active' => true,
        ]);
    }

    public function test_customer_can_place_order_with_gallon_exchange(): void
    {
        $this->actingAs($this->customer);

        $response = $this->post(route('customer.orders.store'), [
            'customer_address_id' => $this->address->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                    'is_exchange' => 1,
                ]
            ],
            'payment_method' => 'cod',
            'customer_notes' => 'Tolong diantar sebelum jam 12',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'subtotal' => 21000, // 3 * 7000
            'delivery_fee' => 3000,
            'total_amount' => 24000,
            'payment_method' => 'cod',
        ]);

        $order = Order::where('user_id', $this->customer->id)->first();
        $response->assertRedirect(route('customer.orders.show', $order));

        // Check stock reduction
        $this->assertEquals(97, $this->product->fresh()->stock_quantity);

        // Check stock movement log
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'sale',
            'quantity' => -3,
            'stock_after' => 97,
        ]);
    }

    public function test_customer_can_place_order_without_exchange_adds_deposit(): void
    {
        $this->actingAs($this->customer);

        // Buying 2 new gallons without empty bottle exchange
        $this->post(route('customer.orders.store'), [
            'customer_address_id' => $this->address->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'is_exchange' => 0,
                ]
            ],
            'payment_method' => 'cod',
        ]);

        // Each gallon is 7000 (water) + 35000 (deposit) = 42000 * 2 = 84000 + 3000 ongkir = 87000
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'subtotal' => 84000,
            'delivery_fee' => 3000,
            'total_amount' => 87000,
        ]);
    }

    public function test_customer_can_cancel_pending_order_and_stock_is_returned(): void
    {
        $this->actingAs($this->customer);

        // Place order for 5 gallons
        $this->post(route('customer.orders.store'), [
            'customer_address_id' => $this->address->id,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 5, 'is_exchange' => 1]
            ],
            'payment_method' => 'cod',
        ]);

        $this->assertEquals(95, $this->product->fresh()->stock_quantity);
        $order = Order::where('user_id', $this->customer->id)->first();

        // Customer cancels order
        $cancelResponse = $this->post(route('customer.orders.cancel', $order), [
            'cancellation_reason' => 'Salah pesan jumlah',
        ]);

        $cancelResponse->assertSessionHas('success');
        $this->assertEquals('cancelled', $order->fresh()->status);

        // Stock restored back to 100!
        $this->assertEquals(100, $this->product->fresh()->stock_quantity);

        // Return stock movement logged
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'return',
            'quantity' => 5,
            'stock_after' => 100,
        ]);
    }

    public function test_customer_cannot_cancel_already_in_transit_order(): void
    {
        $this->actingAs($this->customer);

        $order = Order::create([
            'order_number' => 'ORD-TEST-0001',
            'user_id' => $this->customer->id,
            'recipient_name' => 'Budi',
            'recipient_phone' => '0812345',
            'delivery_address_text' => 'Jl. Sukajadi',
            'status' => 'ready_for_delivery',
            'payment_status' => 'unpaid',
            'subtotal' => 14000,
            'total_amount' => 14000,
        ]);

        $response = $this->post(route('customer.orders.cancel', $order), [
            'cancellation_reason' => 'Mau batal',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('ready_for_delivery', $order->fresh()->status);
    }

    public function test_order_generates_valid_whatsapp_confirmation_url(): void
    {
        $this->actingAs($this->customer);

        $order = Order::create([
            'order_number' => 'ORD-WA-TEST',
            'user_id' => $this->customer->id,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '08123456789',
            'delivery_address_text' => 'Jl. Sukajadi No. 50',
            'delivery_latitude' => -6.8850,
            'delivery_longitude' => 107.5960,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 14000,
            'delivery_fee' => 3000,
            'total_amount' => 17000,
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => 'Air RO Murni Galon',
            'unit' => 'galon',
            'price' => 7000,
            'cost_price' => 2000,
            'quantity' => 2,
            'is_exchange' => true,
            'subtotal' => 14000,
        ]);

        $url = $order->whatsapp_url;

        $this->assertStringStartsWith('https://wa.me/', $url);
        $this->assertStringContainsString('ORD-WA-TEST', $url);
        $this->assertStringContainsString(rawurlencode('Budi Santoso'), $url);
        $this->assertStringContainsString(rawurlencode('Air RO Murni Galon'), $url);
        $this->assertStringContainsString(rawurlencode('17.000'), $url);
    }
}
