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
        $query = Order::with(['user', 'items', 'delivery'])->latest();

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

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'user.profile',
            'items.product',
            'statusHistories.changer',
            'delivery.statusHistories.changer',
            'delivery.photos',
            'delivery.locations',
            'payments.receiver',
            'review',
        ]);

        return view('admin.orders.show', compact('order'));
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

            // Auto-create delivery if order is confirmed/processing/ready_for_delivery and delivery doesn't exist
            if (in_array($newStatus, ['confirmed', 'processing', 'ready_for_delivery']) && ! $order->delivery) {
                Delivery::create([
                    'delivery_number' => 'DEL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'order_id' => $order->id,
                    'status' => 'assigned',
                ]);
            }

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
