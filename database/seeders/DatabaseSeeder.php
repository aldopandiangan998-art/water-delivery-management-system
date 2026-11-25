<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use App\Models\CustomerAddress;
use App\Models\CustomerProfile;
use App\Models\Delivery;
use App\Models\DeliveryLocation;
use App\Models\DeliveryStatusHistory;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Models\ServiceArea;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Business Settings
        $settings = [
            'company_name' => 'Toko Nova Jaya - AquaFlow Fresh & Pure',
            'company_tagline' => 'Depot Air Minum Higienis & Antar Cepat Langsung ke Rumah Anda',
            'company_phone' => '0881-0261-46848',
            'company_whatsapp' => '6283186109775',
            'company_address' => 'Jl. Bratang Gede VI H No. 130, Kelurahan Ngagelrejo, Kecamatan Wonokromo, Kota Surabaya, Jawa Timur 60245',
            'company_email' => 'support@aquaflowdelivery.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234-5678-90',
            'bank_account_name' => 'PT AQUAFLOW FRESH INDONESIA',
            'business_hours' => 'Senin - Minggu: 07:00 - 20:00 WIB',
            'store_latitude' => '-7.303488',
            'store_longitude' => '112.753373',
            'min_order_amount' => '15000',
        ];

        foreach ($settings as $key => $val) {
            BusinessSetting::set($key, $val, 'general');
        }

        // 2. Service Areas (Kota Surabaya)
        $areaGubeng = ServiceArea::create([
            'name' => 'Kecamatan Gubeng',
            'district' => 'Kota Surabaya',
            'postal_code' => '60281',
            'delivery_fee' => 0,
            'min_order_amount' => 14000,
            'estimated_delivery_time' => '20-35 menit',
            'is_active' => true,
            'notes' => 'Gratis ongkir untuk area Gubeng (minimal 2 galon)',
        ]);

        $areaWonokromo = ServiceArea::create([
            'name' => 'Kecamatan Wonokromo',
            'district' => 'Kota Surabaya',
            'postal_code' => '60241',
            'delivery_fee' => 3000,
            'min_order_amount' => 18000,
            'estimated_delivery_time' => '30-45 menit',
            'is_active' => true,
            'notes' => 'Ongkir terjangkau untuk area Wonokromo, Darmo & Joyoboyo',
        ]);

        $areaTegalsari = ServiceArea::create([
            'name' => 'Kecamatan Tegalsari',
            'district' => 'Kota Surabaya',
            'postal_code' => '60262',
            'delivery_fee' => 2000,
            'min_order_amount' => 16000,
            'estimated_delivery_time' => '25-40 menit',
            'is_active' => true,
            'notes' => 'Area Tunjungan, Dr. Soetomo, dan sekitarnya',
        ]);

        $areaRungkut = ServiceArea::create([
            'name' => 'Kecamatan Rungkut',
            'district' => 'Kota Surabaya',
            'postal_code' => '60293',
            'delivery_fee' => 4000,
            'min_order_amount' => 20000,
            'estimated_delivery_time' => '35-50 menit',
            'is_active' => true,
            'notes' => 'Area Rungkut Industri & Rungkut Asri',
        ]);

        // 3. Users: Admin and Customers
        $superAdmin = User::create([
            'name' => 'Owner Admin',
            'email' => 'owner@aquaflow.com',
            'phone' => '0811-1111-2222',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $admin = User::create([
            'name' => 'Admin Operasional',
            'email' => 'admin@aquaflow.com',
            'phone' => '0881-0261-46848',
            'role' => 'admin',
            'is_active' => true,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $customer1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'customer@aquaflow.com',
            'phone' => '0815-5555-6666',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        CustomerProfile::create([
            'user_id' => $customer1->id,
            'customer_type' => 'household',
            'gallon_deposit_balance' => 3,
            'notes' => 'Pelanggan rutin keluarga, 2 galon per 4 hari',
        ]);

        $customerAddress1 = CustomerAddress::create([
            'user_id' => $customer1->id,
            'label' => 'Rumah Gubeng',
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '0815-5555-6666',
            'address_line' => 'Jl. Kertajaya Indah No. 25 RT 03/RW 04, Gubeng',
            'benchmark' => 'Pagar abu-abu, seberang masjid Al-Ikhlas',
            'service_area_id' => $areaGubeng->id,
            'latitude' => -7.275500,
            'longitude' => 112.755000,
            'is_default' => true,
        ]);

        $customer2 = User::create([
            'name' => 'Ibu Siti Aminah (Warung Berkah)',
            'email' => 'siti@gmail.com',
            'phone' => '0816-6666-7777',
            'role' => 'customer',
            'is_active' => true,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        CustomerProfile::create([
            'user_id' => $customer2->id,
            'customer_type' => 'retail_shop',
            'gallon_deposit_balance' => 8,
            'notes' => 'Warung kelontong, pesan 5-10 galon per minggu',
        ]);

        $customerAddress2 = CustomerAddress::create([
            'user_id' => $customer2->id,
            'label' => 'Warung Berkah Wonokromo',
            'recipient_name' => 'Ibu Siti',
            'recipient_phone' => '0816-6666-7777',
            'address_line' => 'Jl. Darmo Trade Center No. 58 RT 02/RW 01, Wonokromo',
            'benchmark' => 'Spanduk Warung Berkah samping konter pulsa',
            'service_area_id' => $areaWonokromo->id,
            'latitude' => -7.305000,
            'longitude' => 112.735000,
            'is_default' => true,
        ]);

        // 4. Product Categories
        $catGalon = ProductCategory::create([
            'name' => 'Air Minum Galon',
            'slug' => 'air-minum-galon',
            'description' => 'Air minum isi ulang dan galon bermerek kualitas higienis teruji lab',
            'is_active' => true,
        ]);

        $catKemasan = ProductCategory::create([
            'name' => 'Air Kemasan Botol & Dus',
            'slug' => 'air-kemasan-botol-dus',
            'description' => 'Air minum dalam kemasan praktis botol dan cup dus',
            'is_active' => true,
        ]);

        $catAksesoris = ProductCategory::create([
            'name' => 'Aksesoris & Perlengkapan',
            'slug' => 'aksesoris-perlengkapan',
            'description' => 'Pompa galon, guci dispenser, kran, dan tutup segel',
            'is_active' => true,
        ]);

        // 5. Products
        $p1 = Product::create([
            'category_id' => $catGalon->id,
            'name' => 'Air Galon Isi Ulang RO (Reverse Osmosis)',
            'slug' => 'air-galon-isi-ulang-ro',
            'description' => 'Air minum murni hasil filtrasi membran RO 0.0001 mikron + UV & Ozon. Sangat segar, higienis, TDS < 10 ppm.',
            'unit' => 'galon',
            'price' => 7000,
            'cost_price' => 2000,
            'is_returnable_gallon' => true,
            'deposit_price' => 35000,
            'tracks_inventory' => true,
            'stock_quantity' => 120,
            'low_stock_threshold' => 25,
            'is_active' => true,
        ]);

        $p2 = Product::create([
            'category_id' => $catGalon->id,
            'name' => 'Air Galon Mineral Bio-Energy',
            'slug' => 'air-galon-mineral-bio-energy',
            'description' => 'Air pegunungan alami kaya mineral esensial untuk kesehatan keluarga, filtrasi 8 tingkat + batuan bio-keramik.',
            'unit' => 'galon',
            'price' => 6000,
            'cost_price' => 1800,
            'is_returnable_gallon' => true,
            'deposit_price' => 35000,
            'tracks_inventory' => true,
            'stock_quantity' => 150,
            'low_stock_threshold' => 30,
            'is_active' => true,
        ]);

        $p3 = Product::create([
            'category_id' => $catGalon->id,
            'name' => 'Galon Aqua 19L (Refill Resmi)',
            'slug' => 'galon-aqua-19l-refill-resmi',
            'description' => 'Isi ulang galon asli Danone Aqua bersegel hologram pabrik resmi.',
            'unit' => 'galon',
            'price' => 22000,
            'cost_price' => 18500,
            'is_returnable_gallon' => true,
            'deposit_price' => 55000,
            'tracks_inventory' => true,
            'stock_quantity' => 45,
            'low_stock_threshold' => 15,
            'is_active' => true,
        ]);

        $p4 = Product::create([
            'category_id' => $catGalon->id,
            'name' => 'Galon Le Minerale 15L (Sekali Pakai)',
            'slug' => 'galon-le-minerale-15l',
            'description' => 'Galon sekali pakai dengan tutup ulir kedap udara, 100% BPA Free, tidak perlu tukar galon kosong.',
            'unit' => 'galon',
            'price' => 23000,
            'cost_price' => 19500,
            'is_returnable_gallon' => false,
            'deposit_price' => 0,
            'tracks_inventory' => true,
            'stock_quantity' => 40,
            'low_stock_threshold' => 10,
            'is_active' => true,
        ]);

        $p5 = Product::create([
            'category_id' => $catKemasan->id,
            'name' => 'Air Mineral Dus Gelas 220ml (Isi 48 cup)',
            'slug' => 'air-mineral-dus-gelas-220ml',
            'description' => 'Cocok untuk acara keluarga, arisan, pengajian, dan rapat kantor.',
            'unit' => 'dus',
            'price' => 25000,
            'cost_price' => 20500,
            'is_returnable_gallon' => false,
            'deposit_price' => 0,
            'tracks_inventory' => true,
            'stock_quantity' => 60,
            'low_stock_threshold' => 15,
            'is_active' => true,
        ]);

        $p6 = Product::create([
            'category_id' => $catKemasan->id,
            'name' => 'Air Mineral Botol 600ml (Dus 24 botol)',
            'slug' => 'air-mineral-botol-600ml-dus',
            'description' => 'Air mineral higienis botol 600ml untuk bepergian dan konsumsi tamu.',
            'unit' => 'dus',
            'price' => 46000,
            'cost_price' => 38000,
            'is_returnable_gallon' => false,
            'deposit_price' => 0,
            'tracks_inventory' => true,
            'stock_quantity' => 35,
            'low_stock_threshold' => 10,
            'is_active' => true,
        ]);

        $p7 = Product::create([
            'category_id' => $catAksesoris->id,
            'name' => 'Pompa Galon Elektrik Otomatis USB Rechargeable',
            'slug' => 'pompa-galon-elektrik-otomatis',
            'description' => 'Praktis tinggal pencet, baterai tahan hingga 30-40 hari pemakaian normal, kabel micro-USB included.',
            'unit' => 'unit',
            'price' => 45000,
            'cost_price' => 28000,
            'is_returnable_gallon' => false,
            'deposit_price' => 0,
            'tracks_inventory' => true,
            'stock_quantity' => 18,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        // Record Initial Stock Movements
        $allProducts = [$p1, $p2, $p3, $p4, $p5, $p6, $p7];
        foreach ($allProducts as $prod) {
            StockMovement::create([
                'product_id' => $prod->id,
                'type' => 'in',
                'quantity' => $prod->stock_quantity,
                'stock_before' => 0,
                'stock_after' => $prod->stock_quantity,
                'reference_type' => 'Initial Stock',
                'reference_id' => 1,
                'notes' => 'Stok awal pembukaan sistem',
                'created_by' => $superAdmin->id,
            ]);
        }

        // 6. Seed Past Completed Order & Review
        $orderPast = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-0001',
            'user_id' => $customer1->id,
            'customer_address_id' => $customerAddress1->id,
            'recipient_name' => $customerAddress1->recipient_name,
            'recipient_phone' => $customerAddress1->recipient_phone,
            'delivery_address_text' => $customerAddress1->address_line,
            'delivery_benchmark' => $customerAddress1->benchmark,
            'delivery_latitude' => $customerAddress1->latitude,
            'delivery_longitude' => $customerAddress1->longitude,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'subtotal' => 21000,
            'delivery_fee' => 0,
            'discount' => 0,
            'total_amount' => 21000,
            'empty_gallons_returned' => 3,
            'customer_notes' => 'Tolong antar sebelum jam 11 siang ya mas',
            'admin_notes' => 'Pesanan rutin pelanggan',
            'completed_at' => Carbon::yesterday()->setHour(10)->setMinute(45),
            'created_at' => Carbon::yesterday()->setHour(9)->setMinute(15),
        ]);

        OrderItem::create([
            'order_id' => $orderPast->id,
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'unit' => $p1->unit,
            'price' => $p1->price,
            'cost_price' => $p1->cost_price,
            'quantity' => 3,
            'is_exchange' => true,
            'subtotal' => 21000,
            'notes' => 'Tukar 3 galon kosong RO',
        ]);

        OrderStatusHistory::create([
            'order_id' => $orderPast->id,
            'status' => 'pending',
            'notes' => 'Pesanan dibuat oleh pelanggan',
            'changed_by' => $customer1->id,
            'created_at' => Carbon::yesterday()->setHour(9)->setMinute(15),
        ]);

        OrderStatusHistory::create([
            'order_id' => $orderPast->id,
            'status' => 'confirmed',
            'notes' => 'Pesanan dikonfirmasi oleh admin',
            'changed_by' => $admin->id,
            'created_at' => Carbon::yesterday()->setHour(9)->setMinute(25),
        ]);

        OrderStatusHistory::create([
            'order_id' => $orderPast->id,
            'status' => 'completed',
            'notes' => 'Pesanan telah diantar dan dibayar tunai',
            'changed_by' => $admin->id,
            'created_at' => Carbon::yesterday()->setHour(10)->setMinute(45),
        ]);

        // Delivery for past order
        $deliveryPast = Delivery::create([
            'delivery_number' => 'DEL-' . date('Ymd') . '-0001',
            'order_id' => $orderPast->id,
            'status' => 'delivered',
            'started_at' => Carbon::yesterday()->setHour(10)->setMinute(10),
            'arrived_at' => Carbon::yesterday()->setHour(10)->setMinute(35),
            'completed_at' => Carbon::yesterday()->setHour(10)->setMinute(45),
        ]);

        DeliveryStatusHistory::create([
            'delivery_id' => $deliveryPast->id,
            'status' => 'assigned',
            'notes' => 'Ditugaskan ke Admin Operasional',
            'changed_by' => $admin->id,
            'created_at' => Carbon::yesterday()->setHour(9)->setMinute(30),
        ]);

        DeliveryStatusHistory::create([
            'delivery_id' => $deliveryPast->id,
            'status' => 'delivered',
            'notes' => 'Pengantaran sukses',
            'changed_by' => $admin->id,
            'created_at' => Carbon::yesterday()->setHour(10)->setMinute(45),
        ]);

        // Payment for past order
        Payment::create([
            'payment_number' => 'PAY-' . date('Ymd') . '-0001',
            'order_id' => $orderPast->id,
            'payment_method' => 'cod',
            'amount' => 21000,
            'status' => 'paid',
            'notes' => 'Uang pas Rp 21.000 diterima petugas di tempat',
            'received_by' => $admin->id,
            'paid_at' => Carbon::yesterday()->setHour(10)->setMinute(45),
        ]);

        // Review for past order
        Review::create([
            'order_id' => $orderPast->id,
            'user_id' => $customer1->id,
            'rating' => 5,
            'comment' => 'Airnya sangat jernih dan segar, galon bersih disegel rapat. Pelayanan admin sangat ramah dan cepat sampai. Mantap!',
            'is_approved' => true,
            'approved_by' => $admin->id,
            'created_at' => Carbon::yesterday()->setHour(12)->setMinute(0),
        ]);

        // 7. Seed Active In-Transit Order for Live Tracking Demo!
        $orderActive = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-0002',
            'user_id' => $customer1->id,
            'customer_address_id' => $customerAddress1->id,
            'recipient_name' => $customerAddress1->recipient_name,
            'recipient_phone' => $customerAddress1->recipient_phone,
            'delivery_address_text' => $customerAddress1->address_line,
            'delivery_benchmark' => $customerAddress1->benchmark,
            'delivery_latitude' => $customerAddress1->latitude,
            'delivery_longitude' => $customerAddress1->longitude,
            'status' => 'ready_for_delivery',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 28000,
            'delivery_fee' => 0,
            'discount' => 0,
            'total_amount' => 28000,
            'empty_gallons_returned' => 4,
            'customer_notes' => 'Titip di teras jika saya sedang ke warung sebelah',
            'created_at' => now()->subMinutes(25),
        ]);

        OrderItem::create([
            'order_id' => $orderActive->id,
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'unit' => $p1->unit,
            'price' => $p1->price,
            'cost_price' => $p1->cost_price,
            'quantity' => 4,
            'is_exchange' => true,
            'subtotal' => 28000,
            'notes' => 'Tukar 4 galon',
        ]);

        $deliveryActive = Delivery::create([
            'delivery_number' => 'DEL-' . date('Ymd') . '-0002',
            'order_id' => $orderActive->id,
            'status' => 'in_transit',
            'started_at' => now()->subMinutes(12),
        ]);

        // Seed realistic GPS path moving from Depot Toko Nova Jaya (-7.303488, 112.753373) toward customer in Surabaya
        $routePoints = [
            ['-7.303488', '112.753373', 12],
            ['-7.292000', '112.754200', 9],
            ['-7.288000', '112.754800', 6],
            ['-7.282000', '112.755200', 3],
            ['-7.275500', '112.755000', 1],
        ];

        foreach ($routePoints as $pt) {
            DeliveryLocation::create([
                'delivery_id' => $deliveryActive->id,
                'latitude' => $pt[0],
                'longitude' => $pt[1],
                'accuracy' => 5.2,
                'heading' => 210,
                'speed' => 22.5,
                'recorded_at' => now()->subMinutes($pt[2]),
            ]);
        }

        // 8. Seed Sample Expenses
        Expense::create([
            'expense_number' => 'EXP-' . date('Ymd') . '-0001',
            'category' => 'fuel',
            'title' => 'BBM Motor Operasional Antar Galon (Pertalite)',
            'amount' => 50000,
            'date' => Carbon::today(),
            'notes' => 'Isi full tangki motor beat D 4512 ABC',
            'recorded_by' => $admin->id,
        ]);

        Expense::create([
            'expense_number' => 'EXP-' . date('Ymd') . '-0002',
            'category' => 'stock',
            'title' => 'Pembelian Tutup Segel Galon & Tisu Antiseptik (1 Dus)',
            'amount' => 125000,
            'date' => Carbon::yesterday(),
            'notes' => '1000 pcs tutup galon biru + 1000 pcs tisu pembersih galon',
            'recorded_by' => $admin->id,
        ]);
    }
}
