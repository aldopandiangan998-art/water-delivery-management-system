<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function __construct(
        protected WhatsAppNotificationService $waService
    ) {}

    /**
     * Show QRIS DANA & WhatsApp Gateway integration settings
     */
    public function index()
    {
        $danaWebhookUrl = url('/api/webhooks/dana-notification');
        $danaSecret = BusinessSetting::get('dana_webhook_secret') ?: config('services.dana.webhook_secret', 'dana-novajaya-secret-2026');
        $fonnteToken = BusinessSetting::get('fonnte_api_token') ?: config('services.fonnte.token', '');
        $adminPhone = BusinessSetting::get('company_whatsapp', '62881026146848');

        return view('admin.settings.integrations', compact(
            'danaWebhookUrl',
            'danaSecret',
            'fonnteToken',
            'adminPhone'
        ));
    }

    /**
     * Save integration settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'dana_webhook_secret' => ['required', 'string', 'min:6', 'max:100'],
            'fonnte_api_token' => ['nullable', 'string', 'max:100'],
            'company_whatsapp' => ['required', 'string', 'max:25'],
        ]);

        BusinessSetting::set('dana_webhook_secret', trim($validated['dana_webhook_secret']));
        BusinessSetting::set('fonnte_api_token', trim($validated['fonnte_api_token'] ?? ''));
        BusinessSetting::set('company_whatsapp', trim($validated['company_whatsapp']));

        return back()->with('success', 'Pengaturan integrasi QRIS DANA dan WhatsApp Gateway berhasil diperbarui.');
    }

    /**
     * Test sending a sample WhatsApp message to admin number
     */
    public function testWhatsApp(Request $request)
    {
        $adminPhone = BusinessSetting::get('company_whatsapp', '62881026146848');
        $token = WhatsAppNotificationService::getToken();

        if (empty($token)) {
            return back()->with('error', 'Gagal kirim tes: API Token Fonnte belum diisi. Silakan masukkan token dari akun Fonnte Anda terlebih dahulu.');
        }

        $now = now()->translatedFormat('d F Y H:i:s');
        $testMessage = "🤖 *TES KONEKSI WHATSAPP GATEWAY (AQUAFLOW)*\n\n"
            . "Halo Admin Toko Nova Jaya!\n"
            . "Jika pesan ini masuk ke WhatsApp Anda, berarti integrasi otomatis Fonnte sudah BERHASIL dan TERHUBUNG sempurna.\n\n"
            . "⏰ Waktu Uji: {$now}\n"
            . "🚀 Siap menerima notifikasi pesanan baru & pembayaran QRIS DANA secara real-time.";

        $result = $this->waService->send($adminPhone, $testMessage);

        if ($result['success']) {
            return back()->with('success', 'Tes pengiriman WhatsApp BERHASIL! Silakan periksa pesan masuk di nomor WhatsApp Admin (' . $adminPhone . ').');
        }

        $detail = $result['response']['reason'] ?? $result['message'] ?? $result['error'] ?? 'Periksa apakah nomor WA di Fonnte sudah berstatus CONNECTED.';
        return back()->with('error', 'Gagal mengirim pesan WhatsApp: ' . $detail);
    }

    /**
     * Get live WhatsApp QR code directly from Fonnte API for scanning inside AquaFlow
     */
    public function getLiveQr()
    {
        $token = WhatsAppNotificationService::getToken();
        if (empty($token)) {
            return response()->json([
                'status' => false,
                'message' => 'Fonnte API Token belum dikonfigurasi.',
            ], 422);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $token,
            ])->asForm()->timeout(15)->post('https://api.fonnte.com/qr', [
                'type' => 'qr',
            ]);

            $result = $response->json();
            $connected = ! empty($result['connected']);
            $qrBase64 = ! empty($result['url']) ? 'data:image/png;base64,' . $result['url'] : null;

            return response()->json([
                'status' => true,
                'connected' => $connected,
                'qr_url' => $qrBase64,
                'message' => $result['message'] ?? '',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil QR dari Fonnte: ' . $e->getMessage(),
            ], 500);
        }
    }
}
