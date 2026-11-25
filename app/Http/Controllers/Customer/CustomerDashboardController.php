<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\ServiceArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load('profile', 'addresses');

        // Check for currently active delivery or order
        $activeOrder = Order::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'ready_for_delivery'])
            ->with(['delivery.driver', 'items.product', 'address'])
            ->latest()
            ->first();

        // Recent completed or other orders
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['items.product', 'delivery'])
            ->latest()
            ->take(5)
            ->get();

        $totalOrdersCount = Order::where('user_id', $user->id)->count();
        $completedOrdersCount = Order::where('user_id', $user->id)->where('status', 'completed')->count();
        $activeOrdersCount = Order::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'ready_for_delivery'])
            ->count();

        $popularProducts = \App\Models\Product::where('is_active', true)->take(4)->get();
        $defaultAddress = $user->addresses->firstWhere('is_default', true) ?? $user->addresses->first();

        return view('customer.dashboard', compact(
            'user',
            'activeOrder',
            'recentOrders',
            'totalOrdersCount',
            'completedOrdersCount',
            'activeOrdersCount',
            'popularProducts',
            'defaultAddress'
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        $user->load('profile', 'addresses');
        return view('customer.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'customer_type' => ['required', 'string', 'in:household,retail_shop,office,restaurant'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];

        if (! empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        if ($user->profile) {
            $user->profile->update([
                'customer_type' => $validated['customer_type'],
            ]);
        }

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    public function storeProfile()
    {
        $settings = [
            'company_name' => BusinessSetting::get('company_name', 'Toko Nova Jaya - AquaFlow Fresh & Pure'),
            'company_tagline' => BusinessSetting::get('company_tagline', 'Depot Air Minum Higienis & Antar Cepat Langsung ke Rumah Anda'),
            'company_phone' => BusinessSetting::get('company_phone', '0881-0261-46848'),
            'company_whatsapp' => BusinessSetting::get('company_whatsapp', '6283186109775'),
            'company_address' => BusinessSetting::get('company_address', 'Jl. Bratang Gede VI H No. 130, Kelurahan Ngagelrejo, Kecamatan Wonokromo, Kota Surabaya, Jawa Timur 60245'),
            'company_email' => BusinessSetting::get('company_email', 'support@aquaflowdelivery.com'),
            'business_hours' => BusinessSetting::get('business_hours', 'Senin - Minggu: 07:00 - 20:00 WIB'),
            'store_latitude' => (float) BusinessSetting::get('store_latitude', -7.303488),
            'store_longitude' => (float) BusinessSetting::get('store_longitude', 112.753373),
            'bank_name' => BusinessSetting::get('bank_name', 'BCA'),
            'bank_account_number' => BusinessSetting::get('bank_account_number', '1234-5678-90'),
            'bank_account_name' => BusinessSetting::get('bank_account_name', 'PT AQUAFLOW FRESH INDONESIA'),
            'google_maps_url' => 'https://maps.app.goo.gl/fozU4QCYdbE6unCX7',
        ];

        $serviceAreas = ServiceArea::where('is_active', true)->orderBy('name')->get();

        return view('customer.store-profile', compact('settings', 'serviceAreas'));
    }
}
