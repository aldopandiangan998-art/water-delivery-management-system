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

    public function test_customer_can_view_order_create_page(): void
    {
        $this->actingAs($this->customer);

        $response = $this->get(route('customer.orders.create'));
        $response->assertStatus(200);
        $response->assertSee('Formulir Pemesanan Air Minum');
        $response->assertSee('Air RO Murni Galon');
        $response->assertSee('Rumah Utama');
    }

    public function test_customer_can_place_order_with_delivery_auto_created(): void
    {
        $this->actingAs($this->customer);

        $response = $this->post(route('customer.orders.store'), [
            'customer_address_id' => $this->address->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'is_exchange' => 1,
                ]
            ],
            'payment_method' => 'cod',
            'customer_notes' => 'Pagar hitam depan masjid',
        ]);

        $order = Order::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('customer.orders.show', $order));

        $this->assertEquals('pending', $order->status);
        $this->assertEquals(14000, $order->subtotal);
        $this->assertEquals(3000, $order->delivery_fee);
        $this->assertEquals(17000, $order->total_amount);

        // Verify Delivery is auto-created
        $this->assertNotNull($order->delivery);
        $this->assertEquals('assigned', $order->delivery->status);

        // Verify stock decremented
        $this->assertEquals(98, $this->product->fresh()->stock_quantity);
    }

    public function test_admin_can_complete_delivery_with_proof_photo_from_order_show(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'Admin Depo',
            'email' => 'admin_delivery_test@test.com',
            'phone' => '081299998888',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $order = Order::create([
            'order_number' => 'ORD-PROOF-TEST',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => 'Budi Pelanggan',
            'recipient_phone' => '08123456789',
            'delivery_address_text' => 'Jl. Sukajadi No. 50',
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 14000,
            'delivery_fee' => 3000,
            'total_amount' => 17000,
        ]);

        $photo = UploadedFile::fake()->image('laporan_kirim.jpg');

        $response = $this->actingAs($admin)->post(route('admin.orders.complete-delivery', $order), [
            'photo' => $photo,
            'empty_gallons_returned' => 2,
            'payment_received' => 17000,
            'notes' => 'Air galon telah diterima di lokasi.',
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals(2, $order->empty_gallons_returned);

        // Verify delivery photo stored
        $this->assertNotNull($order->delivery);
        $this->assertEquals('delivered', $order->delivery->status);
        $this->assertCount(1, $order->delivery->photos);
        Storage::disk('public')->assertExists($order->delivery->photos->first()->photo_path);
    }

    public function test_customer_can_submit_text_comment_on_completed_order(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-REVIEW-TEST',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => 'Budi Pelanggan',
            'recipient_phone' => '08123456789',
            'delivery_address_text' => 'Jl. Sukajadi No. 50',
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'subtotal' => 14000,
            'delivery_fee' => 3000,
            'total_amount' => 17000,
        ]);

        $response = $this->actingAs($this->customer)->post(route('customer.orders.review', $order), [
            'comment' => 'Pengantaran sangat cepat dan air sangat segar!',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'user_id' => $this->customer->id,
            'comment' => 'Pengantaran sangat cepat dan air sangat segar!',
            'is_approved' => true,
        ]);
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

    public function test_customer_can_update_delivery_address_when_order_pending(): void
    {
        $this->actingAs($this->customer);

        $order = Order::create([
            'order_number' => 'ORD-TEST-ADDR-1',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => $this->address->recipient_name,
            'recipient_phone' => $this->address->recipient_phone,
            'delivery_address_text' => $this->address->address_line,
            'delivery_latitude' => $this->address->latitude,
            'delivery_longitude' => $this->address->longitude,
            'service_area_id' => $this->serviceArea->id,
            'delivery_fee' => 3000,
            'subtotal' => 14000,
            'total_amount' => 17000,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
        ]);

        $newAddress = CustomerAddress::create([
            'user_id' => $this->customer->id,
            'label' => 'Kantor Baru',
            'recipient_name' => 'Budi Kantor',
            'recipient_phone' => '089988776655',
            'address_line' => 'Jl. Asia Afrika No. 100, Bandung',
            'latitude' => -6.9200,
            'longitude' => 107.6100,
            'is_default' => false,
        ]);

        $response = $this->put(route('customer.orders.update-address', $order), [
            'customer_address_id' => $newAddress->id,
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals($newAddress->id, $order->customer_address_id);
        $this->assertEquals('Budi Kantor', $order->recipient_name);
        $this->assertEquals('089988776655', $order->recipient_phone);
        $this->assertEquals('Jl. Asia Afrika No. 100, Bandung', $order->delivery_address_text);
    }

    public function test_customer_cannot_update_delivery_address_when_order_not_pending(): void
    {
        $this->actingAs($this->customer);

        $order = Order::create([
            'order_number' => 'ORD-TEST-ADDR-2',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => $this->address->recipient_name,
            'recipient_phone' => $this->address->recipient_phone,
            'delivery_address_text' => $this->address->address_line,
            'status' => 'completed',
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'subtotal' => 14000,
            'delivery_fee' => 0,
            'total_amount' => 14000,
        ]);

        $newAddress = CustomerAddress::create([
            'user_id' => $this->customer->id,
            'label' => 'Rumah Nenek',
            'recipient_name' => 'Budi Nenek',
            'recipient_phone' => '08111222333',
            'address_line' => 'Jl. Braga No. 15',
            'is_default' => false,
        ]);

        $response = $this->put(route('customer.orders.update-address', $order), [
            'customer_address_id' => $newAddress->id,
        ]);

        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals($this->address->id, $order->customer_address_id);
    }

    public function test_customer_can_create_address_via_ajax_on_order_page(): void
    {
        $this->actingAs($this->customer);

        $response = $this->postJson(route('customer.addresses.store'), [
            'label' => 'Toko Kelontong Baru',
            'recipient_name' => 'Budi Toko',
            'recipient_phone' => '087788990011',
            'address_line' => 'Jl. Dipatiukur No. 20',
            'benchmark' => 'Samping fotocopy',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Alamat pengiriman berhasil disimpan.',
        ]);

        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $this->customer->id,
            'label' => 'Toko Kelontong Baru',
            'recipient_name' => 'Budi Toko',
            'address_line' => 'Jl. Dipatiukur No. 20',
        ]);
    }

    public function test_order_create_page_shows_visual_address_cards_and_no_select_dropdown(): void
    {
        $this->actingAs($this->customer);

        $response = $this->get(route('customer.orders.create'));
        $response->assertStatus(200);
        $response->assertSee('Alamat Pengantaran');
        $response->assertSee('Tambah Alamat');
        $response->assertDontSee('Pilih Alamat Pengantaran:');
        $response->assertDontSee('<select x-model.number="selectedAddressId"', false);
    }
}
