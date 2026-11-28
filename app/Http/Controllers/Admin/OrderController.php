<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Delivery;
use App\Models\DeliveryLocation;
use App\Models\DeliveryPhoto;
use App\Models\DeliveryStatusHistory;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items', 'delivery'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%{$s}%")
                  ->orWhere('recipient_name', 'like', "%{$s}%")
                  ->orWhere('recipient_phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'user.profile',
            'address',
            'items.product',
            'statusHistories.changer',
            'delivery.statusHistories.changer',
            'delivery.photos',
            'delivery.locations',
            'payments.receiver',
            'review',
        ]);

        $depotLat = (float) BusinessSetting::get('store_latitude', -7.303488);
        $depotLng = (float) BusinessSetting::get('store_longitude', 112.753373);
        $companyName = BusinessSetting::get('company_name', 'Toko Nova Jaya - AquaFlow');
        $companyAddress = BusinessSetting::get('company_address', 'Jl. Bratang Gede VI H No. 130, Kelurahan Ngagelrejo, Kecamatan Wonokromo, Kota Surabaya, Jawa Timur 60245');
        $googleMapsApiKey = config('services.google_maps.api_key') ?: BusinessSetting::get('google_maps_api_key', '');

        return view('admin.orders.show', compact('order', 'depotLat', 'depotLng', 'companyName', 'companyAddress', 'googleMapsApiKey'));
    }

    public function startDelivery(Request $request, Order $order)
    {
        if ($order->status === 'completed') {
            return back()->with('info', 'Pesanan ini sudah selesai.');
        }

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($request, $order, $validated) {
            $delivery = $order->delivery;
            if (! $delivery) {
                $delivery = Delivery::create([
                    'delivery_number' => 'DEL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'order_id' => $order->id,
                    'status' => 'in_transit',
                    'departed_at' => now(),
                ]);
            } else {
                $delivery->update([
                    'status' => 'in_transit',
                    'departed_at' => now(),
                ]);
            }

            // Fallback initial location: use request lat/lng, or depot store coordinates
            $depotLat = (float) BusinessSetting::get('store_latitude', -7.303488);
            $depotLng = (float) BusinessSetting::get('store_longitude', 112.753373);

            $initLat = !empty($validated['latitude']) ? (float) $validated['latitude'] : $depotLat;
            $initLng = !empty($validated['longitude']) ? (float) $validated['longitude'] : $depotLng;

            DeliveryLocation::create([
                'delivery_id' => $delivery->id,
                'latitude' => $initLat,
                'longitude' => $initLng,
                'recorded_at' => now(),
            ]);

            DeliveryStatusHistory::create([
                'delivery_id' => $delivery->id,
                'status' => 'in_transit',
                'notes' => 'Pengantaran dimulai oleh admin dan pelacakan GPS aktif.',
                'changed_by' => Auth::id(),
            ]);

            $order->update([
                'status' => 'ready_for_delivery',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'ready_for_delivery',
                'notes' => $validated['notes'] ?? 'Admin sedang dalam perjalanan mengantarkan pesanan air ke alamat pelanggan.',
                'changed_by' => Auth::id(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengantaran dimulai! GPS pelacakan aktif membagikan lokasi ke pelanggan.',
                    'delivery_id' => $delivery->id,
                    'status' => 'in_transit',
                    'initial_lat' => $initLat,
                    'initial_lng' => $initLng,
                    'update_location_url' => route('admin.deliveries.location', $delivery),
                ]);
            }

            return back()->with('success', 'Pengantaran dimulai! Pelanggan kini dapat melacak pergerakan pengantaran secara live.');
        });
    }

    public function completeDelivery(Request $request, Order $order)
    {
        if ($order->status === 'completed') {
            return back()->with('info', 'Pesanan ini sudah selesai.');
        }

        $validated = $request->validate([
            'photo' => ['nullable', 'image', 'max:5120'], // 5MB
            'empty_gallons_returned' => ['nullable', 'integer', 'min:0'],
            'payment_received' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $order, $validated) {
            // Ensure delivery exists
            $delivery = $order->delivery;
            if (! $delivery) {
                $delivery = Delivery::create([
                    'delivery_number' => 'DEL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'order_id' => $order->id,
                    'status' => 'delivered',
                    'completed_at' => now(),
                ]);
            } else {
                $delivery->update([
                    'status' => 'delivered',
                    'completed_at' => now(),
                ]);
            }

            // Handle Proof Photo upload
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('deliveries/proofs', 'public');
                DeliveryPhoto::create([
                    'delivery_id' => $delivery->id,
                    'photo_path' => $path,
                    'caption' => 'Bukti serah terima pesanan #' . $order->order_number,
                    'taken_at' => now(),
                ]);
            }

            DeliveryStatusHistory::create([
                'delivery_id' => $delivery->id,
                'status' => 'delivered',
                'notes' => 'Pesanan berhasil diserahkan kepada pelanggan dan foto bukti dicatat.',
                'changed_by' => Auth::id(),
            ]);

            // Update order to completed
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
                'empty_gallons_returned' => $validated['empty_gallons_returned'] ?? $order->empty_gallons_returned,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'completed',
                'notes' => $validated['notes'] ?? 'Pengantaran air berhasil diselesaikan oleh admin/depot.',
                'changed_by' => Auth::id(),
            ]);

            // If COD and unpaid, record payment
            if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                $amountPaid = isset($validated['payment_received']) && $validated['payment_received'] > 0
                    ? $validated['payment_received']
                    : $order->total_amount;

                Payment::create([
                    'payment_number' => 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'amount' => $amountPaid,
                    'payment_method' => 'cod',
                    'status' => 'paid',
                    'paid_at' => now(),
                    'received_by' => Auth::id(),
                    'notes' => 'Pembayaran tunai COD diterima saat serah terima air.',
                ]);

                $order->update([
                    'payment_status' => 'paid',
                ]);
            }
        });

        return back()->with('success', 'Pesanan berhasil diselesaikan dan foto laporan kirim air telah disimpan!');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,processing,ready_for_delivery,completed,cancelled,rejected'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $oldStatus = $order->status;
            $newStatus = $validated['status'];

            $order->update([
                'status' => $newStatus,
                'admin_notes' => $validated['notes'] ?? $order->admin_notes,
                'completed_at' => $newStatus === 'completed' ? now() : $order->completed_at,
                'cancelled_at' => in_array($newStatus, ['cancelled', 'rejected']) ? now() : $order->cancelled_at,
            ]);

            // Auto-create delivery if order is confirmed/processing/ready_for_delivery and delivery doesn't exist
            if (in_array($newStatus, ['confirmed', 'processing', 'ready_for_delivery']) && ! $order->delivery) {
                Delivery::create([
                    'delivery_number' => 'DEL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'order_id' => $order->id,
                    'status' => 'assigned',
                ]);
            }

            // If cancelled or rejected, cancel linked delivery if active
            if (in_array($newStatus, ['cancelled', 'rejected'])) {
                if ($order->delivery && in_array($order->delivery->status, ['assigned', 'not_assigned', 'in_transit'])) {
                    $order->delivery->update([
                        'status' => 'cancelled',
                    ]);

                    DeliveryStatusHistory::create([
                        'delivery_id' => $order->delivery->id,
                        'status' => 'cancelled',
                        'notes' => "Pengantaran dibatalkan karena status pesanan diubah menjadi {$newStatus} oleh admin " . Auth::user()->name,
                        'changed_by' => Auth::id(),
                    ]);
                }
            }

            // If cancelled or rejected, restock inventory
            if (in_array($newStatus, ['cancelled', 'rejected']) && ! in_array($oldStatus, ['cancelled', 'rejected'])) {
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
                            'reference_type' => 'Order ' . ucfirst($newStatus),
                            'reference_id' => $order->id,
                            'notes' => "Pengembalian stok pembatalan #{$order->order_number}",
                            'created_by' => Auth::id(),
                        ]);
                    }
                }
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newStatus,
                'notes' => $validated['notes'] ?? "Status diubah menjadi {$newStatus} oleh admin " . Auth::user()->name,
                'changed_by' => Auth::id(),
            ]);
        });

        return back()->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui.");
    }

    public function updatePayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_status' => ['required', 'in:unpaid,partially_paid,paid,refunded'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $order->update([
                'payment_status' => $validated['payment_status'],
            ]);

            if ($validated['payment_status'] === 'paid') {
                $amount = $validated['amount_paid'] ?? $order->total_amount;
                Payment::create([
                    'payment_number' => 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'order_id' => $order->id,
                    'payment_method' => $order->payment_method,
                    'amount' => $amount,
                    'status' => 'paid',
                    'notes' => $validated['notes'] ?? 'Diverifikasi oleh admin ' . Auth::user()->name,
                    'received_by' => Auth::id(),
                    'paid_at' => now(),
                ]);
            }
        });

        return back()->with('success', "Status pembayaran pesanan #{$order->order_number} berhasil diperbarui.");
    }

    public function updateCoordinates(Request $request, Order $order)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $order->update([
            'delivery_latitude' => $validated['latitude'],
            'delivery_longitude' => $validated['longitude'],
        ]);

        if ($order->customer_address_id && $order->address && !$order->address->latitude) {
            $order->address->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Titik koordinat tujuan pengantaran berhasil disimpan.',
            'latitude' => (float) $order->delivery_latitude,
            'longitude' => (float) $order->delivery_longitude,
        ]);
    }

    public function saveGoogleMapsKey(Request $request)
    {
        $validated = $request->validate([
            'api_key' => ['nullable', 'string', 'max:255'],
        ]);

        $key = trim($validated['api_key'] ?? '');
        BusinessSetting::set('google_maps_api_key', $key);

        return response()->json([
            'success' => true,
            'message' => $key 
                ? 'Google Maps API Key berhasil disimpan dan diaktifkan.' 
                : 'Google Maps API Key dihapus. Sistem beralih ke Leaflet OpenStreetMap.',
            'api_key' => $key,
        ]);
    }

    /**
     * Download printable 80mm thermal receipt PDF
     */
    public function downloadReceipt(Request $request, Order $order)
    {
        $order->load(['items.product', 'user', 'address']);

        $pdf = Pdf::loadView('orders.receipt-pdf', compact('order'));
        $pdf->setPaper([0, 0, 226.77, 600], 'portrait');

        if ($request->has('stream')) {
            return $pdf->stream("struk-{$order->order_number}.pdf");
        }

        return $pdf->download("struk-{$order->order_number}.pdf");
    }

    /**
     * Get real-time pending orders count and latest pending order info for admin visual alerts
     */
    public function pendingCount()
    {
        $count = Order::where('status', 'pending')->count();
        $latest = Order::where('status', 'pending')->latest()->first();

        return response()->json([
            'count' => $count,
            'latest' => $latest ? [
                'id' => $latest->id,
                'order_number' => $latest->order_number,
                'recipient_name' => $latest->recipient_name,
                'total_amount' => number_format($latest->total_amount, 0, ',', '.'),
                'time_ago' => $latest->created_at->diffForHumans(),
                'url' => route('admin.orders.show', $latest),
            ] : null,
        ]);
    }
}
