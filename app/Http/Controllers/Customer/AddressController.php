<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\ServiceArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = CustomerAddress::where('user_id', Auth::id())
            ->with('serviceArea')
            ->orderBy('is_default', 'desc')
            ->get();

        $serviceAreas = ServiceArea::where('is_active', true)->get();

        return view('customer.addresses.index', compact('addresses', 'serviceAreas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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

        $hasAddresses = CustomerAddress::where('user_id', Auth::id())->exists();
        $isDefault = $hasAddresses ? ($request->boolean('is_default')) : true;

        if ($isDefault) {
            CustomerAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        $newAddress = CustomerAddress::create([
            'user_id' => Auth::id(),
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

        if ($request->expectsJson() || $request->ajax()) {
            $newAddress->load('serviceArea');
            return response()->json([
                'success' => true,
                'message' => 'Alamat pengiriman berhasil disimpan.',
                'address' => $newAddress,
            ]);
        }

        return back()->with('success', 'Alamat pengiriman berhasil disimpan.');
    }

    public function update(Request $request, CustomerAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
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

        if ($request->boolean('is_default')) {
            CustomerAddress::where('user_id', Auth::id())->update(['is_default' => false]);
            $address->is_default = true;
        }

        $address->update($validated);

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(CustomerAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = CustomerAddress::where('user_id', Auth::id())->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return back()->with('success', 'Alamat berhasil dihapus.');
    }

    public function setDefault(CustomerAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        CustomerAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('success', 'Alamat utama berhasil diubah.');
    }
}
