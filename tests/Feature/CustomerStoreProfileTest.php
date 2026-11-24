<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\ServiceArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerStoreProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        BusinessSetting::set('company_name', 'Toko Nova Jaya - AquaFlow Fresh & Pure');
        BusinessSetting::set('company_address', 'Jl. Bratang Gede VI H No. 130, Kelurahan Ngagelrejo, Kecamatan Wonokromo, Kota Surabaya');
        BusinessSetting::set('store_latitude', '-7.303488');
        BusinessSetting::set('store_longitude', '112.753373');

        ServiceArea::create([
            'name' => 'Kecamatan Wonokromo',
            'district' => 'Kota Surabaya',
            'delivery_fee' => 0,
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'name' => 'Pelanggan Surabaya',
            'email' => 'pelanggan@test.com',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);
    }

    public function test_guest_is_redirected_from_customer_store_profile(): void
    {
        $response = $this->get(route('customer.store-profile'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_view_store_profile_and_maps(): void
    {
        $this->actingAs($this->customer);

        $response = $this->get(route('customer.store-profile'));

        $response->assertOk();
        $response->assertSee('Toko Nova Jaya');
        $response->assertSee('Bratang Gede');
        $response->assertSee('storeLocationMap');
        $response->assertSee('maps.app.goo.gl/fozU4QCYdbE6unCX7');
        $response->assertSee('-7.303488');
        $response->assertSee('112.753373');
    }
}
