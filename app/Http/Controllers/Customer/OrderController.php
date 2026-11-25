<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Models\StockMovement;
use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::where('user_id', Auth::id())
            ->with(['items.product', 'delivery.driver', 'review'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('customer.orders.index', compact('orders'));
    }

    public function create()
    {
        $user = Auth::user();
        $addresses = CustomerAddress::where('user_id', $user->id)->with('serviceArea')->get();
        $categories = ProductCategory::where('is_active', true)->with(['products' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        $products = Product::where('is_active', true)->orderBy('category_id')->get();

        return view('customer.orders.create', compact('user', 'addresses', 'categories', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_address_id' => ['required', 'exists:customer_addresses,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.is_exchange' => ['nullable', 'boolean'],
            'payment_method' => ['required', 'in:cod,qris,bank_transfer'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $address = CustomerAddress::where('id', $validated['customer_address_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return DB::transaction(function () use ($validated, $address, $request) {
            $subtotal = 0;
            $itemsData = [];
            $totalEmptyReturnable = 0;

            foreach ($validated['items'] as $itemInput) {
                $product = Product::lockForUpdate()->findOrFail($itemInput['product_id']);
                $quantity = (int) $itemInput['quantity'];
                $isExchange = !empty($itemInput['is_exchange']);

                // Calculate unit price: if returnable gallon but NOT exchanging empty bottle, add deposit price
                $unitPrice = $product->price;
                if ($product->is_returnable_gallon && !$isExchange && $product->deposit_price > 0) {
                    $unitPrice += $product->deposit_price;
                }

                $itemSubtotal = $unitPrice * $quantity;
                $subtotal += $itemSubtotal;

                if ($product->is_returnable_gallon && $isExchange) {
                    $totalEmptyReturnable += $quantity;
                }

                // Check stock
                if ($product->tracks_inventory && $product->stock_quantity < $quantity) {
                    throw new \Exception("Stok {$product->name} tidak mencukupi (tersisa {$product->stock_quantity}).");
                }

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'cost_price' => $product->cost_price,
                    'is_exchange' => $isExchange,
                    'subtotal' => $itemSubtotal,
                ];
            }

            // Calculate delivery fee
            $deliveryFee = 0;
            if ($address->serviceArea) {
                $deliveryFee = (float) $address->serviceArea->delivery_fee;
            }

            $totalAmount = $subtotal + $deliveryFee;

            // Generate unique order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'customer_address_id' => $address->id,
                'recipient_name' => $address->recipient_name,
                'recipient_phone' => $address->recipient_phone,
                'delivery_address_text' => $address->address_line,
                'delivery_benchmark' => $address->benchmark,
                'delivery_latitude' => $address->latitude,
                'delivery_longitude' => $address->longitude,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => 0,
                'total_amount' => $totalAmount,
                'empty_gallons_returned' => $totalEmptyReturnable,
                'customer_notes' => $validated['customer_notes'] ?? null,
            ]);

            // Create items and reduce stock
            foreach ($itemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'unit' => $item['product']->unit,
                    'price' => $item['unit_price'],
                    'cost_price' => $item['cost_price'],
                    'quantity' => $item['quantity'],
                    'is_exchange' => $item['is_exchange'],
                    'subtotal' => $item['subtotal'],
                ]);

                if ($item['product']->tracks_inventory) {
                    $stockBefore = $item['product']->stock_quantity;
                    $item['product']->decrement('stock_quantity', $item['quantity']);
                    $stockAfter = $item['product']->stock_quantity;

                    StockMovement::create([
                        'product_id' => $item['product']->id,
                        'type' => 'sale',
                        'quantity' => -$item['quantity'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => 'Order',
                        'reference_id' => $order->id,
                        'notes' => "Pesanan #{$order->order_number}",
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            // Create initial status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'notes' => 'Pesanan berhasil dibuat oleh pelanggan.',
                'changed_by' => Auth::id(),
            ]);

            if ($order->payment_method === 'cod') {
                try {
                    app(WhatsAppNotificationService::class)->sendOrderNotification($order);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Gagal mengirim auto WA COD: ' . $e->getMessage());
                }

                return redirect()->route('customer.orders.show', $order)
                    ->with('auto_open_whatsapp', true)
                    ->with('success', "Pesanan #{$order->order_number} berhasil dibuat! Mengarahkan ke WhatsApp Admin untuk konfirmasi pengantaran (COD)...");
            }

            return redirect()->route('customer.orders.show', $order)
                ->with('info', "Pesanan #{$order->order_number} berhasil dibuat! Silakan selesaikan pembayaran QRIS di bawah dan klik 'Saya Sudah Bayar' agar pesanan diteruskan ke WhatsApp Admin.");
        });
    }

    public function confirmPayment(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'proof_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,heic', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');
            $filename = 'proof-' . $order->order_number . '-' . time() . '.' . $file->getClientOriginalExtension();
            $proofPath = $file->storeAs('payment_proofs', $filename, 'public');
        }

        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'payment_number' => 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                'payment_method' => $order->payment_method ?? 'qris',
                'amount' => $order->total_amount,
                'status' => 'unpaid',
                'proof_image' => $proofPath ?? $order->payments->first()?->proof_image,
                'notes' => $validated['notes'] ?? 'Dikonfirmasi oleh pelanggan via web.',
            ]
        );

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'notes' => 'Pelanggan telah mengonfirmasi pembayaran QRIS dan mengirimkan data transfer.',
            'changed_by' => Auth::id(),
        ]);

        try {
            app(WhatsAppNotificationService::class)->sendOrderNotification($order);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim auto WA QRIS confirmation: ' . $e->getMessage());
        }

        return redirect()->route('customer.orders.show', $order)
            ->with('auto_open_whatsapp', true)
            ->with('payment_confirmed', true)
            ->with('success', "Pembayaran QRIS berhasil dikonfirmasi! Notifikasi pesanan sekarang siap diteruskan ke WhatsApp Admin.");
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load([
            'items.product',
            'statusHistories.changer',
            'delivery.driver',
            'delivery.photos',
            'delivery.statusHistories',
            'payments.receiver',
            'review',
        ]);

        return view('customer.orders.show', compact('order'));
    }

    public function track(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['delivery.driver', 'delivery.latestLocation', 'address']);

        if (! $order->delivery) {
            return redirect()->route('customer.orders.show', $order)->with('info', 'Driver belum ditugaskan untuk pengantaran ini.');
        }

        return view('customer.orders.track', compact('order'));
    }

    public function apiLocation(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $delivery = $order->delivery;
        if (! $delivery) {
            return response()->json(['status' => 'no_delivery']);
        }

        $latest = $delivery->latestLocation;
        $isStale = false;
        if ($latest) {
            $isStale = Carbon::parse($latest->recorded_at)->diffInMinutes(now()) >= 5;
        }

        return response()->json([
            'delivery_status' => $delivery->status,
            'driver_name' => $delivery->driver?->name ?? 'Driver AquaFlow',
            'driver_phone' => $delivery->driver?->phone,
            'customer_lat' => (float) $order->delivery_latitude,
            'customer_lng' => (float) $order->delivery_longitude,
            'driver_lat' => $latest ? (float) $latest->latitude : null,
            'driver_lng' => $latest ? (float) $latest->longitude : null,
            'accuracy' => $latest?->accuracy,
            'heading' => $latest?->heading,
            'speed' => $latest?->speed,
            'recorded_at' => $latest?->recorded_at?->diffForHumans() ?? null,
            'recorded_at_raw' => $latest?->recorded_at?->toIso8601String() ?? null,
            'is_stale' => $isStale,
        ]);
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (! in_array($order->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan karena sudah dalam proses pengiriman.');
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $order->update([
                'status' => 'cancelled',
                'cancellation_reason' => $validated['cancellation_reason'],
                'cancelled_at' => now(),
            ]);

            // Restock items
            foreach ($order->items as $item) {
                if ($item->product && $item->product->tracks_inventory) {
                    $stockBefore = $item->product->stock_quantity;
                    $item->product->increment('stock_quantity', $item->quantity);
                    $stockAfter = $item->product->stock_quantity;

                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'type' => 'return',
                        'quantity' => $item->quantity,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => 'Order Cancellation',
                        'reference_id' => $order->id,
                        'notes' => "Pengembalian stok pembatalan #{$order->order_number}",
                        'created_by' => Auth::id(),
                    ]);
                }
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'cancelled',
                'notes' => 'Dibatalkan oleh pelanggan: ' . $validated['cancellation_reason'],
                'changed_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function review(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'completed') {
            return back()->with('error', 'Hanya pesanan yang telah selesai yang dapat diberikan ulasan.');
        }

        if ($order->review) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk pesanan ini.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        Review::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_approved' => true,
        ]);

        return back()->with('success', 'Terima kasih atas ulasan dan penilaian Anda!');
    }

    /**
     * Check real-time payment status for customer live polling
     */
    public function statusCheck(Order $order)
    {
        if ($order->user_id !== Auth::id() && ! in_array(Auth::user()?->role, ['admin', 'super_admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'is_paid' => $order->payment_status === 'paid',
            'is_qris_confirmed' => $order->is_qris_confirmed,
        ]);
    }
}
