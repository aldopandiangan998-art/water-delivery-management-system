<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\ServiceArea;
use App\Models\User;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerAddress::with(['user.profile', 'serviceArea'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('recipient_name', 'like', "%{$s}%")
                  ->orWhere('recipient_phone', 'like', "%{$s}%")
                  ->orWhere('address_line', 'like', "%{$s}%")
                  ->orWhere('benchmark', 'like', "%{$s}%")
                  ->orWhere('label', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('service_area_id')) {
            $query->where('service_area_id', $request->service_area_id);
        }

        $addresses = $query->paginate(15)->withQueryString();
        $customers = User::where('role', 'customer')->orderBy('name')->get();
        $serviceAreas = ServiceArea::where('is_active', true)->orderBy('name')->get();

        return view('admin.addresses.index', compact('addresses', 'customers', 'serviceAreas'));
    }

    public function create(Request $request)
    {
        $customers = User::where('role', 'customer')->where('is_active', true)->orderBy('name')->get();
        $serviceAreas = ServiceArea::where('is_active', true)->orderBy('name')->get();
        $selectedUserId = $request->query('user_id');

        $depotLat = (float) BusinessSetting::get('store_latitude', -7.3005);
        $depotLng = (float) BusinessSetting::get('store_longitude', 112.7525);

        return view('admin.addresses.create', compact('customers', 'serviceAreas', 'selectedUserId', 'depotLat', 'depotLng'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['required', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:500'],
            'benchmark' => ['nullable', 'string', 'max:255'],
            'service_area_id' => ['nullable', 'exists:service_areas,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $hasAddresses = CustomerAddress::where('user_id', $validated['user_id'])->exists();
        $isDefault = $hasAddresses ? $request->boolean('is_default') : true;

        if ($isDefault) {
            CustomerAddress::where('user_id', $validated['user_id'])->update(['is_default' => false]);
        }

        CustomerAddress::create([
            'user_id' => $validated['user_id'],
            'label' => $validated['label'],
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'address_line' => $validated['address_line'],
            'benchmark' => $validated['benchmark'] ?? null,
            'service_area_id' => $validated['service_area_id'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_default' => $isDefault,
        ]);

        return redirect()->route('admin.addresses.index')->with('success', 'Alamat pelanggan berhasil ditambahkan.');
    }

    public function show(CustomerAddress $address)
    {
        $address->load(['user.profile', 'serviceArea']);
        $recentOrders = Order::where('customer_address_id', $address->id)->latest()->take(5)->get();

        return view('admin.addresses.show', compact('address', 'recentOrders'));
    }

    public function edit(CustomerAddress $address)
    {
        $address->load(['user', 'serviceArea']);
        $customers = User::where('role', 'customer')->orderBy('name')->get();
        $serviceAreas = ServiceArea::where('is_active', true)->orderBy('name')->get();

        $depotLat = (float) BusinessSetting::get('store_latitude', -7.3005);
        $depotLng = (float) BusinessSetting::get('store_longitude', 112.7525);

        return view('admin.addresses.edit', compact('address', 'customers', 'serviceAreas', 'depotLat', 'depotLng'));
    }

    public function update(Request $request, CustomerAddress $address)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['required', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'max:500'],
            'benchmark' => ['nullable', 'string', 'max:255'],
            'service_area_id' => ['nullable', 'exists:service_areas,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            CustomerAddress::where('user_id', $validated['user_id'])
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update([
            'user_id' => $validated['user_id'],
            'label' => $validated['label'],
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'address_line' => $validated['address_line'],
            'benchmark' => $validated['benchmark'] ?? null,
            'service_area_id' => $validated['service_area_id'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_default' => $isDefault,
        ]);

        return redirect()->route('admin.addresses.index')->with('success', 'Alamat pelanggan berhasil diperbarui.');
    }

    public function destroy(CustomerAddress $address)
    {
        // Protect from deleting if linked to an active pending/processing order
        $activeOrdersCount = Order::where('customer_address_id', $address->id)
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'ready_for_delivery'])
            ->count();

        if ($activeOrdersCount > 0) {
            return back()->with('error', 'Alamat tidak dapat dihapus karena masih digunakan pada pesanan yang sedang aktif berjalan.');
        }

        $userId = $address->user_id;
        $wasDefault = $address->is_default;

        $address->delete();

        // If the deleted address was default, promote another address if exists
        if ($wasDefault) {
            $nextAddress = CustomerAddress::where('user_id', $userId)->first();
            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
            }
        }

        return redirect()->route('admin.addresses.index')->with('success', 'Alamat pelanggan berhasil dihapus.');
    }

    public function setDefault(CustomerAddress $address)
    {
        CustomerAddress::where('user_id', $address->user_id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', "Alamat '{$address->label}' berhasil ditetapkan sebagai alamat utama pelanggan.");
    }
}
