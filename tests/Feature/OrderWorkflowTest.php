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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function test_order_create_and_store_routes_are_removed_for_customer(): void
    {
        $this->actingAs($this->customer);

        $responseCreate = $this->get('/customer/orders/create');
        $responseCreate->assertStatus(404);

        $responseStore = $this->post('/customer/orders', []);
        $responseStore->assertStatus(405);
    }

    public function test_customer_can_cancel_pending_order_and_stock_is_returned(): void
    {
        $this->actingAs($this->customer);

        $this->product->update(['stock_quantity' => 95]);

        $order = Order::create([
            'order_number' => 'ORD-TEST-CANCEL',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => 'Budi Pelanggan',
            'recipient_phone' => '08123456789',
            'delivery_address_text' => 'Jl. Sukajadi No. 50',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 35000,
            'delivery_fee' => 3000,
            'total_amount' => 38000,
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => 'Air RO Murni Galon',
            'unit' => 'galon',
            'price' => 7000,
            'cost_price' => 2000,
            'quantity' => 5,
            'is_exchange' => true,
            'subtotal' => 35000,
        ]);

        $this->assertEquals(95, $this->product->fresh()->stock_quantity);

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
        $this->assertStringContainsString(rawurlencode('COD'), $url);
    }

    public function test_admin_order_show_displays_google_maps_view_data(): void
    {
        $admin = User::create([
            'name' => 'Admin Depo',
            'email' => 'admin_map@test.com',
            'phone' => '081299998888',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $order = Order::create([
            'order_number' => 'ORD-MAP-TEST',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '08123456789',
            'delivery_address_text' => 'Jl. Kertajaya Indah No. 25, Surabaya',
            'delivery_latitude' => -7.2755,
            'delivery_longitude' => 112.7550,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 14000,
            'delivery_fee' => 3000,
            'total_amount' => 17000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));

        $response->assertStatus(200);
        $response->assertViewHas('depotLat');
        $response->assertViewHas('depotLng');
        $response->assertViewHas('googleMapsApiKey');
        $response->assertSee('Peta Rute &amp; Pengantaran Real-Time', false);
    }

    public function test_admin_can_update_order_coordinates(): void
    {
        $admin = User::create([
            'name' => 'Admin Depo',
            'email' => 'admin_coord@test.com',
            'phone' => '081299998888',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $order = Order::create([
            'order_number' => 'ORD-COORD-TEST',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '08123456789',
            'delivery_address_text' => 'Jl. Kertajaya Indah No. 25, Surabaya',
            'delivery_latitude' => null,
            'delivery_longitude' => null,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 14000,
            'delivery_fee' => 3000,
            'total_amount' => 17000,
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.orders.coordinates', $order), [
            'latitude' => -7.276000,
            'longitude' => 112.756000,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals(-7.276000, (float) $order->delivery_latitude);
        $this->assertEquals(112.756000, (float) $order->delivery_longitude);
    }

    public function test_admin_can_save_google_maps_api_key(): void
    {
        $admin = User::create([
            'name' => 'Admin Depo',
            'email' => 'admin_key@test.com',
            'phone' => '081299998888',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.orders.save-google-maps-key'), [
            'api_key' => 'AIzaSyTEST_KEY_123456789',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'api_key' => 'AIzaSyTEST_KEY_123456789',
        ]);

        $this->assertEquals('AIzaSyTEST_KEY_123456789', \App\Models\BusinessSetting::get('google_maps_api_key'));
    }
}
