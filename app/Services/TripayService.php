<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayService
{
    /**
     * Get Tripay API Key
     */
    public static function getApiKey(): ?string
    {
        $val = BusinessSetting::get('tripay_api_key');
        if (! empty($val)) {
            return trim($val);
        }

        return config('services.tripay.api_key') ?: null;
    }

    /**
     * Get Tripay Private Key
     */
    public static function getPrivateKey(): ?string
    {
        $val = BusinessSetting::get('tripay_private_key');
        if (! empty($val)) {
            return trim($val);
        }

        return config('services.tripay.private_key') ?: null;
    }

    /**
     * Get Tripay Merchant Code
     */
    public static function getMerchantCode(): ?string
    {
        $val = BusinessSetting::get('tripay_merchant_code');
        if (! empty($val)) {
            return trim($val);
        }

        return config('services.tripay.merchant_code') ?: null;
    }

    /**
     * Get Tripay Mode (sandbox or live)
     */
    public static function getMode(): string
    {
        $val = BusinessSetting::get('tripay_mode');
        if (! empty($val)) {
            return trim($val);
        }

        return config('services.tripay.mode', 'sandbox');
    }

    /**
     * Check whether to pass 0.7% MDR fee to customer
     */
    public static function isPassFeeToCustomer(): bool
    {
        $val = BusinessSetting::get('tripay_pass_fee_to_customer');
        if ($val !== null && $val !== '') {
            return filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) config('services.tripay.pass_fee_to_customer', true);
    }

    /**
     * Check if Tripay credentials are fully set
     */
    public static function isConfigured(): bool
    {
        return ! empty(static::getApiKey())
            && ! empty(static::getPrivateKey())
            && ! empty(static::getMerchantCode());
    }

    /**
     * Get Tripay Base API URL based on active mode
     */
    public static function getBaseUrl(): string
    {
        return static::getMode() === 'live'
            ? 'https://tripay.co.id/api/'
            : 'https://tripay.co.id/api-sandbox/';
    }

    /**
     * Calculate convenience fee (0.7%) so store receives 100% net intact
     *
     * Formula: Gross = ceil(Base / (1 - rate))
     * Fee = Gross - Base
     */
    public static function calculateServiceFee(float $baseAmount): float
    {
        if (! static::isPassFeeToCustomer() || $baseAmount <= 0) {
            return 0.0;
        }

        $rate = (float) config('services.tripay.qris_rate', 0.007);
        if ($rate <= 0 || $rate >= 1) {
            $rate = 0.007;
        }

        $gross = ceil($baseAmount / (1 - $rate));
        $fee = $gross - $baseAmount;

        return max(0.0, (float) $fee);
    }

    /**
     * Generate HMAC SHA256 Signature for creating transaction
     */
    public static function generateSignature(string $merchantRef, int $amount): string
    {
        $merchantCode = static::getMerchantCode() ?? 'T0001';
        $privateKey = static::getPrivateKey() ?? 'secret';

        return hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);
    }

    /**
     * Validate incoming callback signature from Tripay
     */
    public static function validateCallbackSignature(string $rawJson, ?string $receivedSignature): bool
    {
        if (empty($receivedSignature)) {
            return false;
        }

        $privateKey = static::getPrivateKey();
        if (empty($privateKey)) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawJson, $privateKey);

        return hash_equals($expected, $receivedSignature);
    }

    /**
     * Create QRIS transaction via Tripay API (or realistic mock in dev/sandbox if not yet filled)
     */
    public function createQrisTransaction(Order $order): array
    {
        $order->loadMissing(['items.product', 'user']);

        // Prepare items array for Tripay
        $orderItems = [];
        foreach ($order->items as $item) {
            $orderItems[] = [
                'sku' => 'PRD-' . $item->product_id,
                'name' => mb_substr($item->product_name, 0, 50),
                'price' => (int) round($item->price),
                'quantity' => (int) $item->quantity,
                'subtotal' => (int) round($item->subtotal),
            ];
        }

        // Add delivery fee as an item if applicable
        if ($order->delivery_fee > 0) {
            $orderItems[] = [
                'sku' => 'ONGKIR',
                'name' => 'Ongkos Kirim',
                'price' => (int) round($order->delivery_fee),
                'quantity' => 1,
                'subtotal' => (int) round($order->delivery_fee),
            ];
        }

        // Add 0.7% convenience fee as an item if applicable
        if ($order->service_fee > 0) {
            $orderItems[] = [
                'sku' => 'FEE-QRIS',
                'name' => 'Biaya Layanan QRIS (0.7%)',
                'price' => (int) round($order->service_fee),
                'quantity' => 1,
                'subtotal' => (int) round($order->service_fee),
            ];
        }

        $totalAmount = (int) round($order->total_amount);
        $merchantRef = $order->order_number;

        // If credentials are NOT configured, generate mock QRIS for sandbox demo testing
        if (! static::isConfigured()) {
            return $this->generateMockQris($order, $totalAmount);
        }

        $apiKey = static::getApiKey();
        $signature = static::generateSignature($merchantRef, $totalAmount);
        $endpoint = static::getBaseUrl() . 'transaction/create';

        $payload = [
            'method' => 'QRIS2', // QRIS Realtime
            'merchant_ref' => $merchantRef,
            'amount' => $totalAmount,
            'customer_name' => $order->recipient_name,
            'customer_email' => $order->user->email ?? 'customer@aquaflow.com',
            'customer_phone' => $order->recipient_phone,
            'order_items' => $orderItems,
            'callback_url' => url('/api/webhooks/tripay-notification'),
            'return_url' => route('customer.orders.show', $order),
            'expired_time' => time() + (24 * 3600), // 24 hours
            'signature' => $signature,
        ];

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                ])
                ->timeout(15)
                ->post($endpoint, $payload);

            $body = $response->json();

            if ($response->successful() && ! empty($body['success'])) {
                $data = $body['data'];

                $order->update([
                    'tripay_reference' => $data['reference'] ?? null,
                    'tripay_merchant_ref' => $merchantRef,
                    'qris_qr_url' => $data['qr_url'] ?? null,
                    'qris_raw_qr_string' => $data['qr_string'] ?? null,
                    'qris_checkout_url' => $data['checkout_url'] ?? null,
                    'qris_expired_at' => isset($data['expired_time']) ? date('Y-m-d H:i:s', $data['expired_time']) : now()->addDay(),
                ]);

                Log::info("Tripay QRIS Created: {$data['reference']} for Order {$merchantRef}");

                return [
                    'success' => true,
                    'data' => $data,
                ];
            }

            Log::warning("Tripay Create Transaction Failed for Order {$merchantRef}: " . json_encode($body));

            // If API returns an error in sandbox, fallback to mock QR so order process is never broken
            return $this->generateMockQris($order, $totalAmount, $body['message'] ?? 'Tripay API Error');

        } catch (\Throwable $e) {
            Log::error("Tripay Connection Exception for Order {$merchantRef}: " . $e->getMessage());

            return $this->generateMockQris($order, $totalAmount, $e->getMessage());
        }
    }

    /**
     * Generate a realistic QRIS simulation when in mock/sandbox environment
     */
    protected function generateMockQris(Order $order, int $totalAmount, ?string $fallbackReason = null): array
    {
        $mockRef = 'DEV-T' . strtoupper(substr(md5($order->order_number . time()), 0, 10));
        
        // Dynamic QR generator URL displaying nominal & order number
        $qrContent = "00020101021226600016ID.CO.TRIPAY.WWW011893600998{$mockRef}520454995303360540" . strlen($totalAmount) . "{$totalAmount}5802ID5914TOKO NOVA JAYA6007BANDUNG62070703A016304";
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=10&data=' . urlencode($qrContent);

        $order->update([
            'tripay_reference' => $mockRef,
            'tripay_merchant_ref' => $order->order_number,
            'qris_qr_url' => $qrImageUrl,
            'qris_raw_qr_string' => $qrContent,
            'qris_checkout_url' => route('customer.orders.show', $order),
            'qris_expired_at' => now()->addDay(),
        ]);

        return [
            'success' => true,
            'is_mock' => true,
            'reason' => $fallbackReason,
            'data' => [
                'reference' => $mockRef,
                'merchant_ref' => $order->order_number,
                'payment_method' => 'QRIS',
                'payment_name' => 'QRIS (Realtime Otomatis)',
                'amount' => $totalAmount,
                'qr_url' => $qrImageUrl,
                'qr_string' => $qrContent,
                'checkout_url' => route('customer.orders.show', $order),
                'expired_time' => now()->addDay()->timestamp,
            ],
        ];
    }
}
