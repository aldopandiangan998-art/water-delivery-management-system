<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceArea;
use Illuminate\Http\Request;

class ServiceAreaController extends Controller
{
    public function index()
    {
        $areas = ServiceArea::withCount('addresses')->latest()->get();
        return view('admin.service-areas.index', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'estimated_delivery_time' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        ServiceArea::create(array_merge($validated, ['is_active' => true]));

        return back()->with('success', "Wilayah layanan {$validated['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, ServiceArea $serviceArea)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'estimated_delivery_time' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $serviceArea->update(array_merge($validated, ['is_active' => $request->boolean('is_active', true)]));

        return back()->with('success', "Wilayah layanan {$serviceArea->name} berhasil diperbarui.");
    }

    public function destroy(ServiceArea $serviceArea)
    {
        $serviceArea->delete();
        return back()->with('success', 'Wilayah layanan berhasil dihapus.');
    }
}
