<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DanaWebhookController extends Controller
{
    public function __construct(
        protected WhatsAppNotificationService $waService
    ) {}

    /**
     * Handle incoming notification webhook from DANA Forwarder (MacroDroid/Notification Listener)
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Verify Secret Key / Token
        $incomingSecret = $request->header('X-Webhook-Secret')
            ?: $request->header('X-Api-Key')
            ?: $request->bearerToken()
            ?: $request->input('secret_key')
            ?: $request->input('secret')
            ?: $request->input('token');

        $expectedSecret = BusinessSetting::get('dana_webhook_secret')
            ?: config('services.dana.webhook_secret', 'dana-novajaya-secret-2026');

        if (empty($incomingSecret) || ! hash_equals($expectedSecret, (string) $incomingSecret)) {
            Log::warning('DANA Webhook: Unauthorized access attempt with invalid secret key.', [
                'ip' => $request->ip(),
                'provided_secret' => $incomingSecret ? '***' . substr($incomingSecret, -4) : 'none',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing webhook secret key.',
            ], 401);
        }

        // 2. Extract Amount & Sender Info
        $amount = $this->extractAmount($request);
        $sender = $this->extractSender($request);

        if ($amount === null || $amount <= 0) {
            Log::warning('DANA Webhook: Unable to extract valid amount from request payload.', [
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid payload: Gagal mengekstrak nominal transfer yang valid dari notifikasi.',
                'raw_text' => $request->input('text') ?: $request->input('message'),
            ], 422);
        }

        Log::info("DANA Webhook: Processing payment notification for amount Rp " . number_format($amount, 0, ',', '.'), [
            'amount' => $amount,
            'sender' => $sender,
        ]);

        // 3. Match latest unpaid QRIS order with EXACT total_amount
        $order = Order::where('payment_method', 'qris')
            ->where('payment_status', 'unpaid')
            ->where('total_amount', $amount)
            ->whereIn('status', ['pending', 'confirmed', 'processing'])
            ->latest()
            ->first();

        if (! $order) {
            Log::warning("DANA Webhook: No matching unpaid QRIS order found for Rp " . number_format($amount, 0, ',', '.'), [
                'amount' => $amount,
                'sender' => $sender,
            ]);

            return response()->json([
                'success' => false,
                'matched' => false,
                'message' => 'Notifikasi diterima, namun tidak ada pesanan QRIS belum lunas dengan nominal pas Rp ' . number_format($amount, 0, ',', '.'),
                'amount' => $amount,
            ], 200);
        }

        // 4. Update order to paid inside DB transaction
        DB::transaction(function () use ($order, $amount, $sender) {
            $order->update([
                'payment_status' => 'paid',
            ]);

            // Create Payment record
            Payment::create([
                'payment_number' => 'PAY-DANA-' . strtoupper(Str::random(8)),
                'order_id' => $order->id,
                'payment_method' => 'qris',
                'amount' => $amount,
                'status' => 'paid',
                'bank_name' => 'DANA (Doppnnstore QRIS)',
                'notes' => 'Terverifikasi otomatis via Notifikasi HP DANA' . ($sender ? " - Pengirim: {$sender}" : ''),
                'paid_at' => now(),
            ]);

            // Add order status history log
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'notes' => 'Pembayaran QRIS DANA sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil diverifikasi otomatis oleh sistem.',
            ]);
        });

        // 5. Send automated WhatsApp notification to Admin
        try {
            $this->waService->sendPaymentSuccessNotification($order->fresh(), $amount, $sender);
        } catch (\Throwable $e) {
            Log::error('DANA Webhook: WhatsApp notification failed after payment confirmation: ' . $e->getMessage());
        }

        Log::info("DANA Webhook: Order #{$order->order_number} marked as PAID successfully.");

        return response()->json([
            'success' => true,
            'matched' => true,
            'message' => "Pembayaran untuk pesanan #{$order->order_number} berhasil diverifikasi Lunas.",
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => (float) $order->total_amount,
                'payment_status' => 'paid',
                'recipient_name' => $order->recipient_name,
            ],
        ]);
    }

    /**
     * Extract numeric amount from request (direct amount field or regex parsed from notification text)
     */
    protected function extractAmount(Request $request): ?float
    {
        // Direct amount input
        if ($request->has('amount')) {
            $rawAmount = $request->input('amount');
            if (is_numeric($rawAmount)) {
                return (float) $rawAmount;
            }
            if (is_string($rawAmount)) {
                $cleaned = preg_replace('/[^0-9]/', '', $rawAmount);
                if (is_numeric($cleaned)) {
                    return (float) $cleaned;
                }
            }
        }

        // Parse from notification text or body
        $text = $request->input('notification_text')
            ?: $request->input('notification_title')
            ?: $request->input('text')
            ?: $request->input('message')
            ?: $request->input('title')
            ?: $request->input('body')
            ?: $request->getContent();

        if (empty($text) || ! is_string($text)) {
            return null;
        }

        // Example DANA notifications:
        // "Kamu menerima uang Rp 20.000 dari..."
        // "Total bayar Rp 45.000 berhasil diterima"
        // "Menerima saldo Rp25.000"
        if (preg_match('/(?:Rp|IDR)\s*([0-9\.\,]+)/i', $text, $matches)) {
            $numberStr = $matches[1];
            // If contains decimal comma at end e.g. "20.000,00"
            if (str_contains($numberStr, ',') && strlen(substr(strrchr($numberStr, ','), 1)) === 2) {
                $parts = explode(',', $numberStr);
                $clean = preg_replace('/[^0-9]/', '', $parts[0]);
                return (float) $clean;
            }
            $clean = preg_replace('/[^0-9]/', '', $numberStr);
            return is_numeric($clean) ? (float) $clean : null;
        }

        return null;
    }

    /**
     * Extract sender name from request or notification text
     */
    protected function extractSender(Request $request): ?string
    {
        if ($request->filled('sender')) {
            return trim((string) $request->input('sender'));
        }

        $text = $request->input('text') ?: $request->input('message') ?: '';
        if (preg_match('/(?:dari|pengirim)\s+([A-Za-z0-9\s\.\-]+?)(?:\s+sebesar|\s+ke|\s+pada|\.|$)/i', $text, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
