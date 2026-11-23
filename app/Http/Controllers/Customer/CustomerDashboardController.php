<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
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
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
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
}
