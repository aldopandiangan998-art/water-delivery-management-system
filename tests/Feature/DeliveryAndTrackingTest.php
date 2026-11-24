<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\CustomerProfile;
use App\Models\Delivery;
use App\Models\DeliveryLocation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeliveryAndTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected User $otherCustomer;
    protected Order $order;
    protected Delivery $delivery;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $this->customer = User::create([
            'name' => 'Customer Budi',
            'email' => 'budi@test.com',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);
        CustomerProfile::create(['user_id' => $this->customer->id, 'gallon_deposit_balance' => 0]);

        $this->otherCustomer = User::create([
            'name' => 'Other Customer',
            'email' => 'other@test.com',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $this->order = Order::create([
            'order_number' => 'ORD-DEL-TEST',
            'user_id' => $this->customer->id,
            'recipient_name' => 'Budi',
            'recipient_phone' => '0812345678',
            'delivery_address_text' => 'Jl. Sukajadi No. 100',
            'delivery_latitude' => -6.8858,
            'delivery_longitude' => 107.5976,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 14000,
            'total_amount' => 14000,
            'empty_gallons_returned' => 2,
        ]);

        $this->delivery = Delivery::create([
            'delivery_number' => 'DEL-TEST-001',
            'order_id' => $this->order->id,
            'driver_id' => $this->admin->id,
            'status' => 'assigned',
        ]);
    }

    public function test_admin_can_start_delivery(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.deliveries.start', $this->delivery));
        $response->assertSessionHas('success');

        $this->assertEquals('in_transit', $this->delivery->fresh()->status);
        $this->assertNotNull($this->delivery->fresh()->started_at);
        $this->assertEquals('ready_for_delivery', $this->order->fresh()->status);
    }

    public function test_admin_can_update_live_gps_coordinates(): void
    {
        $this->actingAs($this->admin);
        $this->delivery->update(['status' => 'in_transit']);

        $response = $this->postJson(route('admin.deliveries.location', $this->delivery), [
            'latitude' => -6.8860,
            'longitude' => 107.5970,
            'accuracy' => 4.5,
            'speed' => 20.0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('delivery_locations', [
            'delivery_id' => $this->delivery->id,
            'driver_id' => $this->admin->id,
            'latitude' => -6.8860,
            'longitude' => 107.5970,
        ]);
    }

    public function test_customer_can_poll_live_driver_location_for_own_order(): void
    {
        $this->actingAs($this->customer);
        $this->delivery->update(['status' => 'in_transit']);

        DeliveryLocation::create([
            'delivery_id' => $this->delivery->id,
            'driver_id' => $this->admin->id,
            'latitude' => -6.8865,
            'longitude' => 107.5965,
            'accuracy' => 5.0,
            'recorded_at' => now(),
        ]);

        $response = $this->getJson(route('customer.orders.api-location', $this->order));

        $response->assertStatus(200);
        $response->assertJson([
            'delivery_status' => 'in_transit',
            'driver_name' => 'Admin Test',
            'driver_lat' => -6.8865,
            'driver_lng' => 107.5965,
            'is_stale' => false,
        ]);
    }

    public function test_other_customer_cannot_track_anothers_order(): void
    {
        $this->actingAs($this->otherCustomer);

        $response = $this->get(route('customer.orders.track', $this->order));
        $response->assertStatus(403);

        $apiResponse = $this->getJson(route('customer.orders.api-location', $this->order));
        $apiResponse->assertStatus(403);
    }

    public function test_admin_can_complete_delivery_with_proof_photo_and_payment(): void
    {
        $this->actingAs($this->admin);
        $this->delivery->update(['status' => 'in_transit']);

        $file = UploadedFile::fake()->image('bukti_antar.jpg');

        $response = $this->post(route('admin.deliveries.complete', $this->delivery), [
            'photo' => $file,
            'empty_gallons_returned' => 2,
            'payment_received' => 14000,
            'driver_notes' => 'Diserahkan langsung ke Pak Budi',
        ]);

        $response->assertRedirect(route('admin.deliveries.index'));
        $this->assertEquals('delivered', $this->delivery->fresh()->status);
        $this->assertEquals('completed', $this->order->fresh()->status);
        $this->assertEquals('paid', $this->order->fresh()->payment_status);

        // Check payment record created
        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'payment_method' => 'cod',
            'amount' => 14000,
            'status' => 'paid',
        ]);

        // Check delivery photo stored
        $this->assertDatabaseHas('delivery_photos', [
            'delivery_id' => $this->delivery->id,
        ]);
    }

    public function test_admin_can_update_depot_location(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.deliveries.update-depot'), [
            'latitude' => -7.2950273,
            'longitude' => 112.7537136,
            'address' => 'Jl. Bratang Gede VI H No. 130, Ngagelrejo, Wonokromo, Surabaya',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'latitude' => -7.2950273,
                'longitude' => 112.7537136,
            ]);

        $this->assertEquals('-7.2950273', \App\Models\BusinessSetting::get('store_latitude'));
        $this->assertEquals('112.7537136', \App\Models\BusinessSetting::get('store_longitude'));
    }

    public function test_admin_can_search_geocode_endpoint(): void
    {
        $this->actingAs($this->admin);

        \Illuminate\Support\Facades\Http::fake([
            'nominatim.openstreetmap.org/*' => \Illuminate\Support\Facades\Http::response([
                [
                    'place_id' => 12345,
                    'lat' => '-7.2950273',
                    'lon' => '112.7537136',
                    'display_name' => 'Jalan Bratang Gede VI, Ngagelrejo, Wonokromo, Surabaya',
                ]
            ], 200),
        ]);

        $response = $this->getJson(route('admin.deliveries.geocode', ['q' => 'Bratang Gede']));

        $response->assertOk()
            ->assertJsonFragment([
                'lat' => '-7.2950273',
                'lon' => '112.7537136',
            ]);
    }
}
