<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryStatusHistory;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items', 'delivery.driver'])->latest();

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
        $drivers = User::whereIn('role', ['admin', 'super_admin'])->where('is_active', true)->get();

        return view('admin.orders.index', compact('orders', 'drivers'));
    }

    public function show(Order $order)
    {
        $order->load([
            'user.profile',
            'items.product',
            'statusHistories.changer',
            'delivery.driver',
            'delivery.statusHistories.changer',
            'delivery.photos',
            'delivery.locations',
            'payments.receiver',
            'review',
        ]);

        $drivers = User::whereIn('role', ['admin', 'super_admin'])->where('is_active', true)->get();

        return view('admin.orders.show', compact('order', 'drivers'));
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

    public function assignDriver(Request $request, Order $order)
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($order, $validated) {
            $driver = User::findOrFail($validated['driver_id']);

            $delivery = $order->delivery;
            if (! $delivery) {
                $delivery = Delivery::create([
                    'delivery_number' => 'DEL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'order_id' => $order->id,
                    'driver_id' => $driver->id,
                    'status' => 'assigned',
                ]);
            } else {
                $delivery->update([
                    'driver_id' => $driver->id,
                    'status' => in_array($delivery->status, ['not_assigned', 'assigned']) ? 'assigned' : $delivery->status,
                ]);
            }

            DeliveryStatusHistory::create([
                'delivery_id' => $delivery->id,
                'status' => 'assigned',
                'notes' => "Pengantaran ditugaskan kepada {$driver->name} oleh " . Auth::user()->name,
                'changed_by' => Auth::id(),
            ]);

            // Update order status to confirmed if still pending
            if ($order->status === 'pending') {
                $order->update(['status' => 'confirmed']);
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'confirmed',
                    'notes' => "Pesanan dikonfirmasi dan ditugaskan ke driver {$driver->name}",
                    'changed_by' => Auth::id(),
                ]);
            }
        });

        return back()->with('success', 'Driver berhasil ditugaskan untuk pengantaran pesanan ini.');
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
}
