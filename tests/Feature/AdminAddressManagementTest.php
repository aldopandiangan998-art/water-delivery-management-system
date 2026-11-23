<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\ServiceArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAddressManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected ServiceArea $serviceArea;
    protected CustomerAddress $address;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $this->customer = User::create([
            'name' => 'Budi Customer',
            'email' => 'budi@test.com',
            'phone' => '0812345678',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $this->serviceArea = ServiceArea::create([
            'name' => 'Kecamatan Sukajadi',
            'delivery_fee' => 3000,
            'min_order_amount' => 14000,
            'is_active' => true,
        ]);

        $this->address = CustomerAddress::create([
            'user_id' => $this->customer->id,
            'label' => 'Rumah Sukajadi',
            'recipient_name' => 'Budi Customer',
            'recipient_phone' => '0812345678',
            'address_line' => 'Jl. Karang Tineung No. 25',
            'benchmark' => 'Pagar abu-abu',
            'service_area_id' => $this->serviceArea->id,
            'latitude' => -6.8872,
            'longitude' => 107.5954,
            'is_default' => true,
        ]);
    }

    public function test_admin_can_view_addresses_list(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.addresses.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Alamat Pelanggan');
        $response->assertSee('Rumah Sukajadi');
        $response->assertSee('Budi Customer');
    }

    public function test_admin_can_filter_addresses_by_search_or_user(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.addresses.index', ['search' => 'Karang Tineung']));
        $response->assertStatus(200);
        $response->assertSee('Rumah Sukajadi');

        $emptyResponse = $this->get(route('admin.addresses.index', ['search' => 'NonExistentStreet']));
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertDontSee('Rumah Sukajadi');
    }

    public function test_admin_can_view_create_address_form(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.addresses.create', ['user_id' => $this->customer->id]));

        $response->assertStatus(200);
        $response->assertSee('Tambah Alamat Pelanggan');
        $response->assertSee($this->customer->name);
    }

    public function test_admin_can_create_customer_address(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.addresses.store'), [
            'user_id' => $this->customer->id,
            'label' => 'Toko Kelontong',
            'recipient_name' => 'Ibu Siti',
            'recipient_phone' => '08198765432',
            'address_line' => 'Jl. Sukasari No. 10',
            'benchmark' => 'Sebelah warung madura',
            'service_area_id' => $this->serviceArea->id,
            'latitude' => -6.8860,
            'longitude' => 107.5970,
            'is_default' => 0,
        ]);

        $response->assertRedirect(route('admin.addresses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('customer_addresses', [
            'user_id' => $this->customer->id,
            'label' => 'Toko Kelontong',
            'recipient_name' => 'Ibu Siti',
            'recipient_phone' => '08198765432',
            'address_line' => 'Jl. Sukasari No. 10',
        ]);
    }

    public function test_admin_can_view_address_detail(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.addresses.show', $this->address));

        $response->assertStatus(200);
        $response->assertSee('Rumah Sukajadi');
        $response->assertSee('Jl. Karang Tineung No. 25');
        $response->assertSee('Budi Customer');
    }

    public function test_admin_can_view_edit_address_form(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.addresses.edit', $this->address));

        $response->assertStatus(200);
        $response->assertSee('Edit Alamat: Rumah Sukajadi');
        $response->assertSee('Jl. Karang Tineung No. 25');
    }

    public function test_admin_can_update_customer_address(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('admin.addresses.update', $this->address), [
            'user_id' => $this->customer->id,
            'label' => 'Rumah Baru Sukajadi',
            'recipient_name' => 'Budi Santoso Baru',
            'recipient_phone' => '081299990000',
            'address_line' => 'Jl. Karang Tineung No. 99 RT 01',
            'benchmark' => 'Pagar hitam tinggi',
            'service_area_id' => $this->serviceArea->id,
            'latitude' => -6.8880,
            'longitude' => 107.5960,
            'is_default' => 1,
        ]);

        $response->assertRedirect(route('admin.addresses.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('Rumah Baru Sukajadi', $this->address->fresh()->label);
        $this->assertEquals('Budi Santoso Baru', $this->address->fresh()->recipient_name);
        $this->assertEquals('Jl. Karang Tineung No. 99 RT 01', $this->address->fresh()->address_line);
        $this->assertTrue($this->address->fresh()->is_default);
    }

    public function test_admin_can_set_address_as_default(): void
    {
        $this->actingAs($this->admin);

        $secondAddress = CustomerAddress::create([
            'user_id' => $this->customer->id,
            'label' => 'Kantor',
            'recipient_name' => 'Budi Kantor',
            'recipient_phone' => '0812345678',
            'address_line' => 'Gedung Wisma lt 3',
            'is_default' => false,
        ]);

        $response = $this->post(route('admin.addresses.set-default', $secondAddress));
        $response->assertSessionHas('success');

        $this->assertTrue($secondAddress->fresh()->is_default);
        $this->assertFalse($this->address->fresh()->is_default);
    }

    public function test_admin_can_delete_unused_customer_address(): void
    {
        $this->actingAs($this->admin);

        $unusedAddress = CustomerAddress::create([
            'user_id' => $this->customer->id,
            'label' => 'Alamat Sementara',
            'recipient_name' => 'Budi',
            'recipient_phone' => '081234',
            'address_line' => 'Jl. Sementara',
            'is_default' => false,
        ]);

        $response = $this->delete(route('admin.addresses.destroy', $unusedAddress));
        $response->assertRedirect(route('admin.addresses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('customer_addresses', ['id' => $unusedAddress->id]);
    }

    public function test_admin_cannot_delete_address_linked_to_active_orders(): void
    {
        $this->actingAs($this->admin);

        Order::create([
            'order_number' => 'ORD-TEST-ADDR',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => 'Budi',
            'recipient_phone' => '08123',
            'delivery_address_text' => 'Jl. Karang Tineung',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 14000,
            'total_amount' => 14000,
        ]);

        $response = $this->delete(route('admin.addresses.destroy', $this->address));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('customer_addresses', ['id' => $this->address->id]);
    }

    public function test_customer_cannot_access_admin_address_management(): void
    {
        $this->actingAs($this->customer);

        $response = $this->get(route('admin.addresses.index'));
        $response->assertStatus(403);

        $postResponse = $this->post(route('admin.addresses.store'), []);
        $postResponse->assertStatus(403);
    }
}
