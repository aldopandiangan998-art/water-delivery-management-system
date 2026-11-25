<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Get configured Fonnte API Token
     */
    public static function getToken(): ?string
    {
        $token = BusinessSetting::get('fonnte_api_token');
        if (! empty($token)) {
            return trim($token);
        }

        return config('services.fonnte.token') ?: null;
    }

    /**
     * Check if Fonnte WhatsApp integration is configured
     */
    public static function isConfigured(): bool
    {
        return ! empty(static::getToken());
    }

    /**
     * Normalize Indonesian phone number to international format (62xxx)
     */
    public static function formatPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        return $clean;
    }

    /**
     * Get Admin/Depot phone number
     */
    public static function getAdminPhone(): string
    {
        $phone = BusinessSetting::get('company_whatsapp', '6283186109775');
        return static::formatPhoneNumber($phone);
    }

    /**
     * Send direct WhatsApp message via Fonnte API Gateway
     */
    public function send(string $targetPhone, string $message): array
    {
        $token = static::getToken();
        $cleanPhone = static::formatPhoneNumber($targetPhone);

        if (empty($token)) {
            Log::info("WhatsApp Notification Skipped (Fonnte API Token not configured). Target: {$cleanPhone}");
            return [
                'success' => false,
                'message' => 'Fonnte API Token belum dikonfigurasi. Pesan tersimpan di log sistem.',
            ];
        }

        try {
            $endpoint = config('services.fonnte.endpoint', 'https://api.fonnte.com/send');
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->post($endpoint, [
                'target' => $cleanPhone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            $result = $response->json();
            $status = $response->successful() && ($result['status'] ?? false);

            if ($status) {
                Log::info("WhatsApp message sent successfully via Fonnte to {$cleanPhone}");
            } else {
                Log::warning("Fonnte API response warning to {$cleanPhone}: " . json_encode($result));
            }

            return [
                'success' => $status,
                'response' => $result,
                'http_status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error("Failed to send WhatsApp message via Fonnte to {$cleanPhone}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send new order or updated order notification to Admin
     */
    public function sendOrderNotification(Order $order): array
    {
        $adminPhone = static::getAdminPhone();

        $itemsText = "";
        $order->loadMissing('items');
        foreach ($order->items as $item) {
            $exchangeNote = $item->is_exchange ? " (Tukar Galon)" : "";
            $itemsText .= "• {$item->quantity}x {$item->product_name}{$exchangeNote} - Rp " . number_format($item->subtotal, 0, ',', '.') . "\n";
        }

        $mapLink = "";
        if ($order->delivery_latitude && $order->delivery_longitude) {
            $mapLink = "\n📍 Link Lokasi: https://maps.google.com/?q={$order->delivery_latitude},{$order->delivery_longitude}";
        }

        $notesText = $order->customer_notes ? "\n📝 Catatan Pelanggan: {$order->customer_notes}" : "";
        $benchmarkText = $order->delivery_benchmark ? "\n🧭 Patokan Alamat: {$order->delivery_benchmark}" : "";

        if ($order->payment_method === 'qris') {
            $statusLabel = $order->payment_status === 'paid' ? "✅ LUNAS (QRIS DANA Masuk)" : "⏳ Menunggu QRIS";
            $header = "🔔 *PESANAN BARU QRIS DITERIMA* 🔔";
        } else {
            $statusLabel = "💵 BAYAR DI TEMPAT (COD)";
            $header = "🔔 *PESANAN BARU COD DITERIMA* 🔔";
        }

        $message = "{$header}\n"
            . "Halo Admin Toko Nova Jaya (AquaFlow), ada pesanan pelanggan baru masuk:\n\n"
            . "📌 *No. Pesanan:* #{$order->order_number}\n"
            . "👤 *Penerima:* {$order->recipient_name} ({$order->recipient_phone})\n"
            . "🏠 *Alamat Pengantaran:* {$order->delivery_address_text}{$benchmarkText}{$mapLink}\n\n"
            . "📦 *Rincian Barang:*\n"
            . $itemsText
            . "🚚 *Ongkir:* Rp " . number_format($order->delivery_fee, 0, ',', '.') . "\n"
            . "💰 *Total Tagihan:* Rp " . number_format($order->total_amount, 0, ',', '.') . "\n"
            . "💳 *Status Pembayaran:* {$statusLabel}"
            . $notesText . "\n\n"
            . "Segera buka sistem untuk menugaskan driver:\n"
            . url("/admin/orders/{$order->id}");

        return $this->send($adminPhone, $message);
    }

    /**
     * Send payment success confirmation to Admin when DANA QRIS is detected
     */
    public function sendPaymentSuccessNotification(Order $order, float $amount, ?string $sender = null): array
    {
        $adminPhone = static::getAdminPhone();

        $senderText = $sender ? " dari *{$sender}*" : "";
        $message = "🎉 *PEMBAYARAN QRIS DANA DITERIMA & LUNAS* 🎉\n\n"
            . "Sistem otomatis mendeteksi transfer masuk{$senderText} sebesar *Rp " . number_format($amount, 0, ',', '.') . "*.\n\n"
            . "📌 *No. Pesanan:* #{$order->order_number}\n"
            . "👤 *Penerima:* {$order->recipient_name} ({$order->recipient_phone})\n"
            . "🏠 *Alamat:* {$order->delivery_address_text}\n"
            . "💰 *Total Pesanan:* Rp " . number_format($order->total_amount, 0, ',', '.') . "\n"
            . "✅ *Status Pembayaran:* LUNAS (Telah diverifikasi otomatis)\n\n"
            . "Silakan langsung proses pengantaran air untuk pelanggan:\n"
            . url("/admin/orders/{$order->id}");

        return $this->send($adminPhone, $message);
    }
}
