<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderEfficiencyPackageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private User $otherCustomer;
    private ServiceArea $serviceArea;
    private CustomerAddress $address;
    private Product $product;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->otherCustomer = User::factory()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->serviceArea = ServiceArea::create([
            'name' => 'Area Depot Utama',
            'delivery_fee' => 5000,
            'is_active' => true,
        ]);

        $this->address = CustomerAddress::create([
            'user_id' => $this->customer->id,
            'service_area_id' => $this->serviceArea->id,
            'label' => 'Rumah',
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'address_line' => 'Jl. Kenanga No. 12',
            'benchmark' => 'Depan Masjid',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'is_default' => true,
        ]);

        $category = ProductCategory::create([
            'name' => 'Air Galon',
            'slug' => 'air-galon',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Galon Aqua 19L',
            'slug' => 'galon-aqua-19l',
            'price' => 20000,
            'cost_price' => 15000,
            'stock_quantity' => 50,
            'tracks_inventory' => true,
            'is_returnable_gallon' => true,
            'deposit_price' => 35000,
            'is_active' => true,
        ]);

        $this->order = Order::create([
            'order_number' => 'ORD-20261127-TEST1',
            'user_id' => $this->customer->id,
            'customer_address_id' => $this->address->id,
            'recipient_name' => $this->address->recipient_name,
            'recipient_phone' => $this->address->recipient_phone,
            'delivery_address_text' => $this->address->address_line,
            'delivery_benchmark' => $this->address->benchmark,
            'delivery_latitude' => $this->address->latitude,
            'delivery_longitude' => $this->address->longitude,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 40000,
            'delivery_fee' => 5000,
            'discount' => 0,
            'total_amount' => 45000,
            'empty_gallons_due' => 2,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'price' => 20000,
            'quantity' => 2,
            'unit' => 'galon',
            'subtotal' => 40000,
            'is_exchange' => true,
        ]);
    }

    public function test_admin_can_download_receipt_pdf(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.orders.receipt', $this->order));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('struk-ORD-20261127-TEST1.pdf', $response->headers->get('content-disposition'));
    }

    public function test_customer_can_download_their_own_receipt_pdf(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.orders.receipt', $this->order));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_customer_cannot_download_other_customer_receipt_pdf(): void
    {
        $response = $this->actingAs($this->otherCustomer)->get(route('customer.orders.receipt', $this->order));

        $response->assertStatus(403);
    }

    public function test_admin_can_poll_pending_orders_count_api(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.orders.pending-count'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'count',
            'latest' => [
                'id',
                'order_number',
                'recipient_name',
                'total_amount',
                'time_ago',
                'url',
            ],
        ]);
        $response->assertJson([
            'count' => 1,
            'latest' => [
                'order_number' => 'ORD-20261127-TEST1',
                'recipient_name' => 'Budi Santoso',
            ],
        ]);
    }

    public function test_customer_can_trigger_1_click_reorder(): void
    {
        $this->order->update(['status' => 'completed']);

        $reorderResponse = $this->actingAs($this->customer)->get(route('customer.orders.reorder', $this->order));

        $reorderResponse->assertRedirect(route('customer.orders.create', ['reorder' => $this->order->id]));
        $reorderResponse->assertSessionHas('info');

        // Verify checkout page renders prefilled data
        $checkoutResponse = $this->actingAs($this->customer)->get(route('customer.orders.create', ['reorder' => $this->order->id]));
        $checkoutResponse->assertStatus(200);
        $checkoutResponse->assertSee('Mode Pesan Ulang Aktif');
        $checkoutResponse->assertSee($this->product->name);
    }

    public function test_customer_cannot_reorder_another_customer_order(): void
    {
        $this->order->update(['status' => 'completed']);

        $response = $this->actingAs($this->otherCustomer)->get(route('customer.orders.reorder', $this->order));

        $response->assertStatus(403);
    }
}
