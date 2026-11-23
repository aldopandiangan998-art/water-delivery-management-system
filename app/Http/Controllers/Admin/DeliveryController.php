<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Delivery;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        $drivers = User::whereIn('role', ['driver', 'admin'])->where('is_active', true)->get();

        return view('admin.deliveries.index', compact('deliveries', 'drivers'));
    }

    public function map()
    {
        $activeDeliveries = Delivery::whereIn('status', ['assigned', 'in_transit', 'arrived'])
            ->with(['order.address', 'driver', 'latestLocation'])
            ->get();

        $depotLat = (float) BusinessSetting::get('store_latitude', -6.8858);
        $depotLng = (float) BusinessSetting::get('store_longitude', 107.5976);
        $companyName = BusinessSetting::get('company_name', 'Depot AquaFlow');

        return view('admin.deliveries.map', compact('activeDeliveries', 'depotLat', 'depotLng', 'companyName'));
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
                'driver_name' => $del->driver?->name ?? 'Belum ada driver',
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
