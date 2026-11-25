<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Services\TripayService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripayWebhookController extends Controller
{
    protected WhatsAppNotificationService $waService;

    public function __construct(WhatsAppNotificationService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Handle incoming Tripay Payment Notification Webhook
     */
    public function handle(Request $request): JsonResponse
    {
        $rawJson = $request->getContent();
        $signature = $request->header('X-Callback-Signature');
        $event = $request->header('X-Callback-Event');

        Log::info("Tripay Webhook Received. Event: {$event}", [
            'payload' => $request->all(),
        ]);

        if ($event !== 'payment_status') {
            return response()->json([
                'success' => false,
                'message' => 'Event type not supported: ' . $event,
            ], 400);
        }

        // Validate signature if Tripay is configured
        if (TripayService::isConfigured() && ! TripayService::validateCallbackSignature($rawJson, $signature)) {
            Log::warning("Tripay Webhook: Invalid callback signature", [
                'received_signature' => $signature,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid callback signature',
            ], 403);
        }

        $data = $request->all();
        $merchantRef = $data['merchant_ref'] ?? null;
        $tripayRef = $data['reference'] ?? null;
        $status = strtoupper($data['status'] ?? '');
        $totalAmount = (float) ($data['total_amount'] ?? 0);

        if (empty($merchantRef) && empty($tripayRef)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing merchant_ref or reference',
            ], 422);
        }

        // Find the matching order
        $order = Order::where('order_number', $merchantRef)
            ->orWhere('tripay_reference', $tripayRef)
            ->first();

        if (! $order) {
            Log::warning("Tripay Webhook: Order not found for Ref: {$merchantRef} / {$tripayRef}");

            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if ($status === 'PAID') {
            if ($order->payment_status === 'paid') {
                return response()->json([
                    'success' => true,
                    'message' => 'Order already marked as paid',
                ]);
            }

            DB::transaction(function () use ($order, $tripayRef, $totalAmount, $data) {
                // Update Order Status
                $newOrderStatus = $order->status === 'pending' ? 'confirmed' : $order->status;
                $order->update([
                    'payment_status' => 'paid',
                    'status' => $newOrderStatus,
                    'tripay_reference' => $tripayRef ?? $order->tripay_reference,
                ]);

                // Create or update Payment Record
                Payment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'payment_number' => 'PAY-TRIPAY-' . ($tripayRef ?? date('YmdHis')),
                        'payment_method' => 'qris',
                        'amount' => $totalAmount > 0 ? $totalAmount : $order->total_amount,
                        'status' => 'paid',
                        'notes' => 'Pembayaran QRIS Tripay Otomatis (Ref: ' . ($tripayRef ?? '-') . ')',
                        'paid_at' => now(),
                    ]
                );

                // Add Audit Trail
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $newOrderStatus,
                    'notes' => 'Pembayaran QRIS Tripay terverifikasi LUNAS otomatis oleh gateway (Ref: ' . ($tripayRef ?? '-') . ').',
                    'changed_by' => null, // Automated system
                ]);

                // Send WhatsApp Notification to Admin
                try {
                    $this->waService->sendPaymentSuccessNotification(
                        $order,
                        $totalAmount > 0 ? $totalAmount : $order->total_amount,
                        'Tripay QRIS Gateway'
                    );
                } catch (\Throwable $e) {
                    Log::error("Failed to send WhatsApp for Tripay Order {$order->order_number}: " . $e->getMessage());
                }
            });

            Log::info("Tripay Webhook: Order {$order->order_number} marked as PAID successfully.");

            return response()->json([
                'success' => true,
                'message' => "Order {$order->order_number} marked as paid successfully.",
            ]);
        }

        if (in_array($status, ['EXPIRED', 'FAILED'])) {
            $order->update([
                'payment_status' => 'unpaid',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'notes' => "Transaksi QRIS Tripay berstatus: {$status}.",
                'changed_by' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Order {$order->order_number} status updated to {$status}.",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Event acknowledged.",
        ]);
    }

    /**
     * Simulate successful payment callback for Sandbox / Demo Testing
     */
    public function simulate(Request $request, Order $order): JsonResponse
    {
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan sudah berstatus lunas.',
            ]);
        }

        $tripayRef = $order->tripay_reference ?: 'SIM-' . strtoupper(substr(uniqid(), -8));

        DB::transaction(function () use ($order, $tripayRef) {
            $newOrderStatus = $order->status === 'pending' ? 'confirmed' : $order->status;
            $order->update([
                'payment_status' => 'paid',
                'status' => $newOrderStatus,
                'tripay_reference' => $tripayRef,
            ]);

            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_number' => 'PAY-SIM-' . strtoupper(substr(uniqid(), -6)),
                    'payment_method' => 'qris',
                    'amount' => $order->total_amount,
                    'status' => 'paid',
                    'notes' => 'Simulasi Pembayaran QRIS Otomatis (Demo/Sandbox)',
                    'paid_at' => now(),
                ]
            );

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newOrderStatus,
                'notes' => 'Simulasi Pembayaran QRIS Tripay Berhasil (LUNAS Otomatis).',
                'changed_by' => auth()->id(),
            ]);

            try {
                $this->waService->sendPaymentSuccessNotification(
                    $order,
                    $order->total_amount,
                    'Simulasi Tripay QRIS'
                );
            } catch (\Throwable $e) {
                Log::error("Simulate WhatsApp error: " . $e->getMessage());
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Pesanan #{$order->order_number} berhasil disimulasikan LUNAS!",
            'order_status' => $order->status,
            'payment_status' => $order->payment_status,
        ]);
    }
}
