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
            ->with(['items.product', 'delivery', 'review'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('customer.orders.index', compact('orders'));
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
                'payment_method' => $order->payment_method ?? 'cod',
                'amount' => $order->total_amount,
                'status' => 'unpaid',
                'proof_image' => $proofPath ?? $order->payments->first()?->proof_image,
                'notes' => $validated['notes'] ?? 'Dikonfirmasi oleh pelanggan via web.',
            ]
        );

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'notes' => 'Pelanggan telah mengonfirmasi pembayaran.',
            'changed_by' => Auth::id(),
        ]);

        return redirect()->route('customer.orders.show', $order)
            ->with('auto_open_whatsapp', true)
            ->with('payment_confirmed', true)
            ->with('success', "Konfirmasi pembayaran berhasil disimpan!");
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load([
            'items.product',
            'statusHistories.changer',
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

        $order->load(['delivery.latestLocation', 'address']);

        if (! $order->delivery) {
            return redirect()->route('customer.orders.show', $order)->with('info', 'Pengantaran belum dijadwalkan untuk pesanan ini.');
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
            'driver_name' => 'Petugas Depot',
            'driver_phone' => null,
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
        if ($order->user_id !== Auth::id() && Auth::user()?->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'is_paid' => $order->payment_status === 'paid',
        ]);
    }
}
