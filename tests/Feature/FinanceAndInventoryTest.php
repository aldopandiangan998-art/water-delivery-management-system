<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FinanceAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Finance Admin',
            'email' => 'finance@test.com',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $cat = ProductCategory::create([
            'name' => 'Kemasan Galon',
            'slug' => 'kemasan-galon',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $cat->id,
            'name' => 'Galon Aqua Asli',
            'slug' => 'galon-aqua-asli',
            'unit' => 'galon',
            'price' => 22000,
            'cost_price' => 18000,
            'stock_quantity' => 20,
            'low_stock_threshold' => 10,
            'tracks_inventory' => true,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_record_operational_expense(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.finance.store-expense'), [
            'category' => 'fuel',
            'title' => 'BBM Motor Kurir Asep (Pertalite)',
            'amount' => 35000,
            'date' => date('Y-m-d'),
            'notes' => 'Isi bensin operasional',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'category' => 'fuel',
            'title' => 'BBM Motor Kurir Asep (Pertalite)',
            'amount' => 35000,
            'recorded_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_adjust_product_stock(): void
    {
        $this->actingAs($this->admin);

        // Restock +30 units
        $response = $this->post(route('admin.products.adjust-stock', $this->product), [
            'type' => 'in',
            'quantity' => 30,
            'notes' => 'Pengiriman truk pabrik Danone',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(50, $this->product->fresh()->stock_quantity);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'type' => 'in',
            'quantity' => 30,
            'stock_before' => 20,
            'stock_after' => 50,
            'notes' => 'Pengiriman truk pabrik Danone',
        ]);
    }
}
