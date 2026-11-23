<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        BusinessSetting::set('company_name', 'AquaFlow Test');

        $cat = ProductCategory::create([
            'name' => 'Air Galon',
            'slug' => 'air-galon',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $cat->id,
            'name' => 'Air Galon RO Test',
            'slug' => 'air-galon-ro-test',
            'description' => 'Air RO Murni',
            'unit' => 'galon',
            'price' => 7000,
            'cost_price' => 2000,
            'is_returnable_gallon' => true,
            'deposit_price' => 35000,
            'tracks_inventory' => true,
            'stock_quantity' => 50,
            'is_active' => true,
        ]);

        ServiceArea::create([
            'name' => 'Sukajadi Area',
            'district' => 'Kota Bandung',
            'delivery_fee' => 0,
            'min_order_amount' => 15000,
            'is_active' => true,
        ]);
    }

    public function test_homepage_loads_successfully_with_products(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('AquaFlow');
        $response->assertSee('Air Galon RO Test');
        $response->assertSee('Sukajadi Area');
    }

    public function test_service_areas_page_loads(): void
    {
        $response = $this->get('/service-areas');

        $response->assertStatus(200);
        $response->assertSee('Sukajadi Area');
        $response->assertSee('Gratis Ongkir');
    }

    public function test_auth_pages_accessible_to_guests(): void
    {
        $loginRes = $this->get('/login');
        $loginRes->assertStatus(200);
        $loginRes->assertSee('Masuk ke Akun Anda');

        $registerRes = $this->get('/register');
        $registerRes->assertStatus(200);
        $registerRes->assertSee('Daftar Akun Pelanggan Baru');
    }
}
