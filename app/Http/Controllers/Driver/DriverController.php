<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use App\Models\Delivery;
use App\Models\DeliveryLocation;
use App\Models\DeliveryPhoto;
use App\Models\DeliveryStatusHistory;
use App\Models\GallonMovement;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $driverId = Auth::id();

        // Deliveries needing action
        $activeDeliveries = Delivery::where('driver_id', $driverId)
            ->whereIn('status', ['assigned', 'in_transit', 'arrived'])
            ->with(['order.items', 'order.address', 'latestLocation'])
            ->latest()
            ->get();

        // Completed history
        $completedDeliveries = Delivery::where('driver_id', $driverId)
            ->whereIn('status', ['delivered', 'failed', 'cancelled'])
            ->with(['order.items', 'photos'])
            ->latest()
            ->paginate(10);

        return view('driver.index', compact('activeDeliveries', 'completedDeliveries'));
    }

    public function show(Delivery $delivery)
    {
        // Admin or assigned driver
        if (! Auth::user()->isAdmin() && $delivery->driver_id !== Auth::id()) {
            abort(403);
        }

        $delivery->load([
            'order.items.product',
            'order.user.profile',
            'order.address',
            'statusHistories.changer',
            'latestLocation',
            'photos',
        ]);

        return view('driver.show', compact('delivery'));
    }

    public function start(Delivery $delivery)
    {
        if (! Auth::user()->isAdmin() && $delivery->driver_id !== Auth::id()) {
            abort(403);
        }

        if ($delivery->status !== 'assigned') {
            return back()->with('error', 'Status pengiriman tidak valid untuk dimulai.');
        }

        DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status' => 'in_transit',
                'started_at' => now(),
            ]);

            DeliveryStatusHistory::create([
                'delivery_id' => $delivery->id,
                'status' => 'in_transit',
                'notes' => 'Driver memulai pengantaran ke lokasi pelanggan.',
                'changed_by' => Auth::id(),
            ]);

            $delivery->order->update(['status' => 'ready_for_delivery']);
            OrderStatusHistory::create([
                'order_id' => $delivery->order_id,
                'status' => 'ready_for_delivery',
                'notes' => 'Driver sedang menuju ke lokasi pengantaran.',
                'changed_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Pengantaran dimulai! Bagikan lokasi GPS Anda tetap aktif di halaman ini.');
    }

    public function arrive(Delivery $delivery)
    {
        if (! Auth::user()->isAdmin() && $delivery->driver_id !== Auth::id()) {
            abort(403);
        }

        if ($delivery->status !== 'in_transit') {
            return back()->with('error', 'Status pengiriman tidak valid.');
        }

        DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status' => 'arrived',
                'arrived_at' => now(),
            ]);

            DeliveryStatusHistory::create([
                'delivery_id' => $delivery->id,
                'status' => 'arrived',
                'notes' => 'Driver telah tiba di alamat tujuan.',
                'changed_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Status diubah: Anda telah tiba di lokasi pelanggan.');
    }

    public function complete(Request $request, Delivery $delivery)
    {
        if (! Auth::user()->isAdmin() && $delivery->driver_id !== Auth::id()) {
            abort(403);
        }

        if (! in_array($delivery->status, ['in_transit', 'arrived'])) {
            return back()->with('error', 'Pengantaran tidak dapat diselesaikan pada status ini.');
        }

        $validated = $request->validate([
            'photo' => ['nullable', 'image', 'max:5120'], // 5MB
            'empty_gallons_returned' => ['nullable', 'integer', 'min:0'],
            'payment_received' => ['nullable', 'numeric', 'min:0'],
            'driver_notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $delivery, $validated) {
            $order = $delivery->order;

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

            // Update delivery
            $delivery->update([
                'status' => 'delivered',
                'completed_at' => now(),
                'driver_notes' => $validated['driver_notes'] ?? null,
            ]);

            DeliveryStatusHistory::create([
                'delivery_id' => $delivery->id,
                'status' => 'delivered',
                'notes' => 'Pesanan berhasil diserahkan kepada pelanggan.',
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
                'notes' => 'Pesanan telah selesai diantar dan diterima.',
                'changed_by' => Auth::id(),
            ]);

            // Handle COD payment receipt if recorded
            if ($order->payment_method === 'cod') {
                $amountPaid = isset($validated['payment_received']) && $validated['payment_received'] > 0
                    ? $validated['payment_received']
                    : $order->total_amount;

                Payment::create([
                    'payment_number' => 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'order_id' => $order->id,
                    'payment_method' => 'cod',
                    'amount' => $amountPaid,
                    'status' => 'paid',
                    'notes' => 'Pembayaran tunai (COD) diterima oleh driver ' . Auth::user()->name,
                    'received_by' => Auth::id(),
                    'paid_at' => now(),
                ]);

                $order->update(['payment_status' => 'paid']);
            }

            // Record gallon balance update for customer
            $emptyReturned = (int) ($validated['empty_gallons_returned'] ?? 0);
            $gallonsDelivered = $order->items->where('is_exchange', true)->sum('quantity');

            if ($emptyReturned > 0 || $gallonsDelivered > 0) {
                $profile = CustomerProfile::firstOrCreate(['user_id' => $order->user_id]);
                // Net change: customer received X gallons, gave back Y empty gallons
                $profile->increment('gallon_deposit_balance', ($gallonsDelivered - $emptyReturned));

                GallonMovement::create([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'gallons_delivered' => $gallonsDelivered,
                    'empty_gallons_returned' => $emptyReturned,
                    'balance_after' => $profile->gallon_deposit_balance,
                    'notes' => "Serah terima pesanan #{$order->order_number}",
                    'created_by' => Auth::id(),
                ]);
            }
        });

        return redirect()->route('driver.dashboard')->with('success', 'Pengantaran berhasil diselesaikan! Data dan bukti serah terima telah tersimpan.');
    }

    public function updateLocation(Request $request, Delivery $delivery)
    {
        if (! Auth::user()->isAdmin() && $delivery->driver_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($delivery->status !== 'in_transit') {
            return response()->json(['status' => 'ignored', 'message' => 'Delivery is not in transit']);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric'],
            'heading' => ['nullable', 'numeric'],
            'speed' => ['nullable', 'numeric'],
        ]);

        $loc = DeliveryLocation::create([
            'delivery_id' => $delivery->id,
            'driver_id' => Auth::id(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'accuracy' => $validated['accuracy'] ?? null,
            'heading' => $validated['heading'] ?? null,
            'speed' => $validated['speed'] ?? null,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'id' => $loc->id,
            'recorded_at' => $loc->recorded_at->toIso8601String(),
        ]);
    }
}
