<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Key Metrics
        $todayOrdersCount = Order::whereDate('created_at', $today)->count();
        $todayRevenue = Order::whereDate('created_at', $today)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $activeDeliveriesCount = Delivery::whereIn('status', ['assigned', 'in_transit', 'arrived'])->count();
        $pendingOrdersCount = Order::where('status', 'pending')->count();

        $monthlyRevenue = Order::where('created_at', '>=', $thisMonth)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $monthlyExpenses = Expense::where('date', '>=', $thisMonth->toDateString())->sum('amount');
        $monthlyNetProfit = $monthlyRevenue - $monthlyExpenses;

        // Low stock products
        $lowStockProducts = Product::where('tracks_inventory', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->get();

        // Recent Orders
        $recentOrders = Order::with(['user', 'items', 'delivery'])
            ->latest()
            ->take(8)
            ->get();

        // Top selling products this month
        $topProducts = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->whereHas('order', function ($q) use ($thisMonth) {
                $q->where('created_at', '>=', $thisMonth)
                  ->whereIn('status', ['confirmed', 'processing', 'ready_for_delivery', 'completed']);
            })
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'todayOrdersCount',
            'todayRevenue',
            'activeDeliveriesCount',
            'pendingOrdersCount',
            'monthlyRevenue',
            'monthlyExpenses',
            'monthlyNetProfit',
            'lowStockProducts',
            'recentOrders',
            'topProducts'
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        if ($request->has('phone')) {
            $user->phone = $validated['phone'];
        }

        if (! empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return back()->with('success', 'Profil akun Anda berhasil diperbarui.');
    }
}
