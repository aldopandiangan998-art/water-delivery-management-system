<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\CustomerProfile;
use App\Models\Delivery;
use App\Models\DeliveryLocation;
use App\Models\DeliveryPhoto;
use App\Models\DeliveryStatusHistory;
use App\Models\GallonMovement;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['order.items', 'order.address', 'driver', 'latestLocation'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        $deliveries = $query->paginate(15)->withQueryString();
        $drivers = User::whereIn('role', ['admin', 'super_admin'])->where('is_active', true)->get();

        return view('admin.deliveries.index', compact('deliveries', 'drivers'));
    }

    public function show(Delivery $delivery)
    {
        $delivery->load([
            'order.items.product',
            'order.user.profile',
            'order.address',
            'statusHistories.changer',
            'latestLocation',
            'photos',
            'driver',
        ]);

        return view('admin.deliveries.show', compact('delivery'));
    }

    public function start(Delivery $delivery)
    {
        if ($delivery->status !== 'assigned') {
            return back()->with('error', 'Status pengantaran tidak valid untuk dimulai.');
        }

        DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status' => 'in_transit',
                'started_at' => now(),
            ]);

            DeliveryStatusHistory::create([
                'delivery_id' => $delivery->id,
                'status' => 'in_transit',
                'notes' => 'Petugas memulai pengantaran ke lokasi tujuan.',
                'changed_by' => Auth::id(),
            ]);

            $delivery->order->update(['status' => 'ready_for_delivery']);
            OrderStatusHistory::create([
                'order_id' => $delivery->order_id,
                'status' => 'ready_for_delivery',
                'notes' => 'Pesanan sedang dalam perjalanan menuju lokasi pelanggan.',
                'changed_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Pengantaran dimulai! Status pesanan kini sedang dalam perjalanan.');
    }

    public function arrive(Delivery $delivery)
    {
        if ($delivery->status !== 'in_transit') {
            return back()->with('error', 'Status pengantaran tidak valid.');
        }

        DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status' => 'arrived',
                'arrived_at' => now(),
            ]);

            DeliveryStatusHistory::create([
                'delivery_id' => $delivery->id,
                'status' => 'arrived',
                'notes' => 'Petugas telah tiba di alamat pelanggan.',
                'changed_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Status diperbarui: Petugas telah tiba di lokasi pelanggan.');
    }

    public function complete(Request $request, Delivery $delivery)
    {
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
                'notes' => 'Pesanan telah selesai diantar dan diserahkan.',
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
                    'notes' => 'Pembayaran tunai (COD) diterima oleh ' . Auth::user()->name,
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

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Pengantaran selesai']);
        }

        return redirect()->route('admin.deliveries.index')->with('success', 'Pengantaran berhasil diselesaikan! Bukti serah terima dan pembayaran telah dicatat.');
    }

    public function updateLocation(Request $request, Delivery $delivery)
    {
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

    public function map()
    {
        $activeDeliveries = Delivery::whereIn('status', ['assigned', 'in_transit', 'arrived'])
            ->with(['order.address', 'driver', 'latestLocation'])
            ->get();

        $depotLat = (float) BusinessSetting::get('store_latitude', -7.2950273);
        $depotLng = (float) BusinessSetting::get('store_longitude', 112.7537136);
        $companyName = BusinessSetting::get('company_name', 'Toko Nova Jaya - AquaFlow');
        $companyAddress = BusinessSetting::get('company_address', 'Jl. Bratang Gede VI H No. 130, Kelurahan Ngagelrejo, Kecamatan Wonokromo, Kota Surabaya, Jawa Timur 60245');

        return view('admin.deliveries.map', compact('activeDeliveries', 'depotLat', 'depotLng', 'companyName', 'companyAddress'));
    }

    public function geocode(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        if (empty($query)) {
            return response()->json([]);
        }

        try {
            $searchQuery = str_contains(strtolower($query), 'surabaya') ? $query : ($query . ' Surabaya');
            $response = Http::withHeaders([
                'User-Agent' => 'AquaFlow-WaterDeliveryApp/1.0 (admin@aquaflow.local)'
            ])->timeout(6)->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'json',
                'q' => $searchQuery,
                'limit' => 6,
                'addressdetails' => 1,
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengambil data dari Maps API',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function updateDepotLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        BusinessSetting::set('store_latitude', (string) $validated['latitude']);
        BusinessSetting::set('store_longitude', (string) $validated['longitude']);
        if (!empty($validated['address'])) {
            BusinessSetting::set('company_address', $validated['address']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Titik lokasi Toko Nova Jaya berhasil diperbarui secara permanen!',
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'address' => $validated['address'] ?? null,
        ]);
    }

    public function apiLiveDrivers()
    {
        $activeDeliveries = Delivery::whereIn('status', ['assigned', 'in_transit', 'arrived'])
            ->with(['order', 'driver', 'latestLocation'])
            ->get();

        $data = $activeDeliveries->map(function ($del) {
            $latest = $del->latestLocation;
            $isStale = false;
            if ($latest) {
                $isStale = Carbon::parse($latest->recorded_at)->diffInMinutes(now()) >= 5;
            }

            return [
                'id' => $del->id,
                'delivery_number' => $del->delivery_number,
                'status' => $del->status,
                'status_label' => $del->status_badge['label'],
                'driver_name' => $del->driver?->name ?? 'Admin / Petugas Pengantar',
                'driver_phone' => $del->driver?->phone,
                'driver_lat' => $latest ? (float) $latest->latitude : null,
                'driver_lng' => $latest ? (float) $latest->longitude : null,
                'driver_speed' => $latest?->speed,
                'recorded_at' => $latest?->recorded_at?->diffForHumans(),
                'is_stale' => $isStale,
                'customer_name' => $del->order?->recipient_name,
                'customer_phone' => $del->order?->recipient_phone,
                'customer_address' => $del->order?->delivery_address_text,
                'customer_lat' => (float) $del->order?->delivery_latitude,
                'customer_lng' => (float) $del->order?->delivery_longitude,
                'order_number' => $del->order?->order_number,
                'total_amount' => number_format($del->order?->total_amount ?? 0, 0, ',', '.'),
            ];
        });

        return response()->json($data);
    }
}
