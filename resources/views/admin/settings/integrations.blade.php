@extends('layouts.admin')

@section('title', 'Pengaturan Integrasi QRIS DANA & WhatsApp')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black text-slate-900 flex items-center gap-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-600 to-cyan-500 flex items-center justify-center text-white shadow-md">
                    <i class="fa-solid fa-plug text-base"></i>
                </div>
                Integrasi QRIS DANA & WhatsApp Otomatis
            </h1>
            <p class="text-xs text-slate-500 mt-1 ml-12">Konfigurasi deteksi pembayaran otomatis dan pengiriman notifikasi ke WhatsApp Admin.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-300 rounded-2xl p-4 flex items-center gap-3 text-sm text-emerald-800">
            <i class="fa-solid fa-circle-check text-emerald-500 text-base shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-rose-50 border border-rose-300 rounded-2xl p-4 flex items-center gap-3 text-sm text-rose-800">
            <i class="fa-solid fa-circle-xmark text-rose-500 text-base shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Section 1: How it works -->
    <div class="bg-gradient-to-br from-teal-50 to-cyan-50 border border-teal-200 rounded-3xl p-6">
        <h2 class="text-sm font-black text-teal-900 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-diagram-project text-teal-600"></i>
            Cara Kerja Sistem Deteksi Otomatis QRIS DANA (0% Biaya)
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-center text-xs">
            <div class="bg-white rounded-2xl p-3 text-center shadow-xs border border-teal-100">
                <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center mx-auto mb-2 text-teal-700 text-lg"><i class="fa-solid fa-qrcode"></i></div>
                <div class="font-bold text-slate-800">Pelanggan Scan & Bayar QRIS</div>
                <div class="text-slate-500 mt-0.5">Nominal pas ke Doppnnstore</div>
            </div>
            <div class="text-center text-teal-400 text-xl hidden sm:block">→</div>
            <div class="bg-white rounded-2xl p-3 text-center shadow-xs border border-teal-100">
                <div class="w-10 h-10 rounded-xl bg-cyan-100 flex items-center justify-center mx-auto mb-2 text-cyan-700 text-lg"><i class="fa-brands fa-android"></i></div>
                <div class="font-bold text-slate-800">HP Toko Terima Notifikasi DANA</div>
                <div class="text-slate-500 mt-0.5">MacroDroid baca & kirim ke server</div>
            </div>
            <div class="text-center text-teal-400 text-xl hidden sm:block">→</div>
            <div class="bg-white rounded-2xl p-3 text-center shadow-xs border border-teal-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center mx-auto mb-2 text-emerald-700 text-lg"><i class="fa-brands fa-whatsapp"></i></div>
                <div class="font-bold text-slate-800">WA Admin Berbunyi Otomatis</div>
                <div class="text-slate-500 mt-0.5">Rincian pesanan langsung terkirim</div>
            </div>
        </div>
    </div>

    <!-- Section 2: Webhook Endpoint Info -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="bg-slate-800 px-6 py-4 flex items-center gap-3">
            <i class="fa-solid fa-webhook text-slate-300 text-sm"></i>
            <span class="font-black text-sm text-white">URL Webhook DANA (Salin ke MacroDroid)</span>
            <span class="ml-auto px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold border border-emerald-500/30">POST</span>
        </div>
        <div class="p-5 space-y-3" x-data="{ copiedUrl: false, copiedSecret: false }">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Endpoint URL Webhook</label>
                <div class="flex items-center gap-2">
                    <code class="flex-1 text-xs bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-teal-700 font-mono break-all">{{ $danaWebhookUrl }}</code>
                    <button @click="navigator.clipboard.writeText('{{ $danaWebhookUrl }}'); copiedUrl = true; setTimeout(() => copiedUrl = false, 2500)"
                            class="shrink-0 px-3 py-2.5 rounded-xl text-xs font-bold bg-teal-600 hover:bg-teal-700 text-white transition flex items-center gap-1.5">
                        <i class="fa-solid" :class="copiedUrl ? 'fa-check' : 'fa-copy'"></i>
                        <span x-text="copiedUrl ? 'Tersalin!' : 'Salin'"></span>
                    </button>
                </div>
            </div>
            <div class="text-xs text-slate-500 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
                <i class="fa-solid fa-lightbulb text-amber-500 mr-1.5"></i>
                Gunakan URL ini sebagai tujuan HTTP POST di aplikasi <b>MacroDroid</b> pada HP Android tempat akun DANA Doppnnstore aktif.
            </div>
        </div>
    </div>

    <!-- Section 3: Settings Form -->
    <form method="POST" action="{{ route('admin.settings.integrations.update') }}" class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        @csrf
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <i class="fa-solid fa-gear text-slate-500 text-sm"></i>
            <span class="font-black text-sm text-slate-800">Pengaturan Keamanan & Token</span>
        </div>
        <div class="p-6 space-y-6">

            <!-- DANA Webhook Secret -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Secret Key Webhook DANA <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="dana_webhook_secret" value="{{ old('dana_webhook_secret', $danaSecret) }}"
                       class="w-full text-sm font-mono border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-400 bg-white"
                       placeholder="Contoh: rahasia-toko-nova-jaya-2026" required>
                <p class="text-xs text-slate-500 mt-1.5">
                    Kunci rahasia ini diisi di MacroDroid sebagai header <code class="bg-slate-100 px-1 rounded">X-Webhook-Secret</code>. Pastikan sama antara HP dan server ini.
                </p>
                @error('dana_webhook_secret')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Admin WhatsApp Number -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Nomor WhatsApp Admin / Toko <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="company_whatsapp" value="{{ old('company_whatsapp', $adminPhone) }}"
                       class="w-full text-sm border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-400 bg-white"
                       placeholder="6283186109775" required>
                <p class="text-xs text-slate-500 mt-1.5">Format internasional tanpa + (contoh: <code class="bg-slate-100 px-1 rounded">6283186109775</code>). Semua notifikasi otomatis dikirim ke nomor ini.</p>
                @error('company_whatsapp')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Fonnte API Token -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Fonnte API Token (WhatsApp Gateway)
                </label>
                <div class="flex gap-2" x-data="{ showToken: false }">
                    <div class="relative flex-1">
                        <input :type="showToken ? 'text' : 'password'" name="fonnte_api_token"
                               value="{{ old('fonnte_api_token', $fonnteToken) }}"
                               class="w-full text-sm font-mono border border-slate-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-teal-500 focus:border-teal-400 bg-white pr-10"
                               placeholder="Token dari akun Fonnte.com Anda">
                        <button type="button" @click="showToken = !showToken"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-xs">
                            <i :class="showToken ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-1.5">
                    Dapatkan token dari <a href="https://fonnte.com" target="_blank" class="text-teal-600 hover:underline font-semibold">Fonnte.com</a> → Daftar gratis → Scan QR WhatsApp → Salin API Token.
                    @if(empty($fonnteToken))
                        <span class="ml-1 text-amber-600 font-semibold">⚠ Belum dikonfigurasi — notifikasi WA otomatis belum aktif.</span>
                    @else
                        <span class="ml-1 text-emerald-600 font-semibold">✓ Token sudah diisi.</span>
                    @endif
                </p>
                @error('fonnte_api_token')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

        </div>
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between gap-3 bg-slate-50">
            <span class="text-xs text-slate-500">Pastikan nomor WA Admin aktif dan siap menerima pesan.</span>
            <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-black bg-teal-600 hover:bg-teal-700 text-white shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                Simpan Pengaturan
            </button>
        </div>
    </form>

    <!-- Live WhatsApp QR Scanner Modal & Trigger (Langsung di AquaFlow) -->
    <div x-data="{
        qrModalOpen: false,
        qrLoading: false,
        qrUrl: null,
        qrConnected: false,
        qrInterval: null,
        openQrModal() {
            this.qrModalOpen = true;
            this.fetchQr();
            this.qrInterval = setInterval(() => {
                if (this.qrModalOpen && !this.qrConnected) {
                    this.fetchQr();
                }
            }, 12000);
        },
        closeQrModal() {
            this.qrModalOpen = false;
            if (this.qrInterval) clearInterval(this.qrInterval);
        },
        async fetchQr() {
            this.qrLoading = !this.qrUrl;
            try {
                const res = await fetch('{{ route('admin.settings.integrations.live-qr') }}');
                const data = await res.json();
                if (data.status) {
                    this.qrConnected = data.connected;
                    if (data.qr_url) {
                        this.qrUrl = data.qr_url;
                    }
                    if (data.connected && this.qrInterval) {
                        clearInterval(this.qrInterval);
                    }
                }
            } catch (e) {
                console.error('Error fetching live QR:', e);
            } finally {
                this.qrLoading = false;
            }
        }
    }" class="bg-gradient-to-r from-teal-500 via-cyan-600 to-blue-600 rounded-3xl p-6 text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-white text-2xl shrink-0">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div>
                <h3 class="text-base font-black">Scan QR Code WhatsApp Toko (Langsung di Sini)</h3>
                <p class="text-xs text-teal-100 mt-0.5">
                    Tidak perlu repot mencari menu di web Fonnte. Klik tombol di samping untuk memunculkan barcode QR Code live langsung di layar ini.
                </p>
            </div>
        </div>
        <button type="button" @click="openQrModal()"
                class="shrink-0 px-5 py-3 rounded-2xl text-xs font-black bg-white hover:bg-slate-100 text-teal-800 shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-camera"></i>
            <span>Buka QR Code WhatsApp</span>
        </button>

        <!-- QR Scanner Modal -->
        <div x-show="qrModalOpen" x-cloak 
             class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 backdrop-blur-xs">
            <div class="relative max-w-sm w-full bg-white rounded-3xl p-6 shadow-2xl text-slate-800 text-center" @click.stop>
                <button type="button" @click="closeQrModal()" 
                        class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 bg-slate-100 rounded-full w-8 h-8 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>

                <h4 class="font-black text-base text-slate-900 mb-1">Scan QR Code WhatsApp Toko</h4>
                <p class="text-xs text-slate-500 mb-4">Nomor Toko: <b>{{ $adminPhone }}</b></p>

                <!-- Connected State -->
                <template x-if="qrConnected">
                    <div class="py-6 space-y-3">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto text-3xl">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <h5 class="font-black text-sm text-emerald-700">WhatsApp Sudah Terhubung!</h5>
                        <p class="text-xs text-slate-600">Perangkat Toko Nova Jaya telah aktif dan siap mengirim notifikasi pesanan otomatis.</p>
                        <button type="button" @click="closeQrModal()" class="mt-2 px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white">
                            Selesai & Tutup
                        </button>
                    </div>
                </template>

                <!-- QR Display State -->
                <template x-if="!qrConnected">
                    <div>
                        <div class="bg-slate-50 border-2 border-dashed border-teal-300 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[260px]">
                            <template x-if="qrLoading">
                                <div class="flex flex-col items-center space-y-2 text-xs text-slate-500">
                                    <i class="fa-solid fa-spinner fa-spin text-2xl text-teal-600"></i>
                                    <span>Memuat QR Code dari server...</span>
                                </div>
                            </template>
                            <template x-if="!qrLoading && qrUrl">
                                <div>
                                    <img :src="qrUrl" alt="QR Code WhatsApp" class="w-56 h-56 rounded-xl mx-auto shadow-sm border border-slate-200">
                                    <div class="text-[11px] text-teal-700 font-bold mt-2 flex items-center justify-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                        <span>QR Aktif (Otomatis refresh)</span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-4 text-left bg-teal-50 border border-teal-200 rounded-xl p-3 text-[11px] text-teal-900 space-y-1">
                            <div class="font-bold flex items-center gap-1">
                                <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                <span>Cara Scan di HP:</span>
                            </div>
                            <ol class="list-decimal list-inside space-y-0.5 text-slate-600">
                                <li>Buka WA di HP nomor <b>{{ $adminPhone }}</b></li>
                                <li>Pilih <b>Perangkat Tertaut (Linked Devices)</b></li>
                                <li>Ketuk <b>Tautkan Perangkat</b> & sorot ke barcode di atas</li>
                            </ol>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Section 3b: Test WhatsApp Section -->
    <div class="bg-emerald-50/60 border border-emerald-200 rounded-3xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
        <div>
            <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i class="fa-brands fa-whatsapp text-emerald-600 text-base"></i>
                Uji Koneksi WhatsApp Gateway
            </h3>
            <p class="text-xs text-slate-600 mt-1">
                Kirim pesan tes sekarang ke nomor WhatsApp Admin (<b>{{ $adminPhone }}</b>) untuk memastikan koneksi Fonnte berfungsi normal.
            </p>
        </div>
        <form method="POST" action="{{ route('admin.settings.integrations.test-whatsapp') }}" class="shrink-0">
            @csrf
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition flex items-center justify-center gap-2">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Kirim Pesan Tes ke WA Admin</span>
            </button>
        </form>
    </div>

    <!-- Section 4: MacroDroid Setup Guide -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <i class="fa-brands fa-android text-slate-500 text-sm"></i>
            <span class="font-black text-sm text-slate-800">Panduan Setup MacroDroid di HP Android Toko (3 Menit)</span>
        </div>
        <div class="p-6">
            <div class="space-y-5 text-sm">

                <div class="flex gap-4 items-start">
                    <div class="shrink-0 w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center font-black text-sm">1</div>
                    <div>
                        <div class="font-bold text-slate-800">Instal MacroDroid di HP Android Toko</div>
                        <div class="text-xs text-slate-500 mt-0.5">Buka Google Play Store → Cari <b>"MacroDroid - Device Automation"</b> → Install (Gratis). HP ini harus sama dengan HP tempat akun DANA <b>Doppnnstore</b> aktif dan menerima notifikasi.</div>
                    </div>
                </div>

                <div class="flex gap-4 items-start">
                    <div class="shrink-0 w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center font-black text-sm">2</div>
                    <div>
                        <div class="font-bold text-slate-800">Buat Makro Baru di MacroDroid</div>
                        <div class="text-xs text-slate-500 mt-0.5">Tap <b>"Add Macro"</b> → beri nama misalnya <b>"DANA QRIS Forwarder"</b>.</div>
                    </div>
                </div>

                <div class="flex gap-4 items-start">
                    <div class="shrink-0 w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center font-black text-sm">3</div>
                    <div>
                        <div class="font-bold text-slate-800">Tambahkan Trigger: Notifikasi DANA</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Tap <b>"Triggers"</b> → <b>"Notification Received"</b>.<br>
                            • Application: pilih <b>"DANA"</b><br>
                            • (Opsional) Filter teks: <code class="bg-slate-100 px-1 rounded">menerima</code> atau <code class="bg-slate-100 px-1 rounded">Rp</code> agar hanya uang masuk yang terdeteksi.
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 items-start">
                    <div class="shrink-0 w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center font-black text-sm">4</div>
                    <div>
                        <div class="font-bold text-slate-800">Tambahkan Action: HTTP Request ke Server</div>
                        <div class="text-xs text-slate-500 mt-0.5 space-y-1">
                            <div>Tap <b>"Actions"</b> → <b>"Connectivity"</b> → <b>"HTTP Request"</b>.</div>
                            <div>• <b>URL:</b> <code class="bg-slate-100 px-1.5 py-0.5 rounded font-mono break-all">{{ $danaWebhookUrl }}</code></div>
                            <div>• <b>Method:</b> POST</div>
                            <div>• <b>Headers:</b> tambah header <code class="bg-slate-100 px-1 rounded">X-Webhook-Secret</code> → isi dengan nilai secret key di atas</div>
                            <div>• <b>Body (JSON):</b>
                                <pre class="mt-1 bg-slate-100 rounded-xl p-3 text-[11px] text-slate-700 overflow-x-auto">{{ '{' }}"text": "[NOTIFICATION_TEXT]", "amount": "", "sender": ""{{ '}' }}</pre>
                                Ganti <code class="bg-slate-100 px-1 rounded">[NOTIFICATION_TEXT]</code> dengan variabel MacroDroid untuk isi notifikasi: <code class="bg-slate-100 px-1 rounded">{notifc_text}</code>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 items-start">
                    <div class="shrink-0 w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-black text-sm"><i class="fa-solid fa-check text-xs"></i></div>
                    <div>
                        <div class="font-bold text-emerald-800">Simpan & Aktifkan Makro</div>
                        <div class="text-xs text-slate-500 mt-0.5">Tap <b>Save</b>. Pastikan makro berstatus <b>Enabled (hijau)</b>. Selesai! Setiap kali DANA Doppnnstore menerima uang, sistem akan otomatis mendeteksi dan mengirim notifikasi ke WhatsApp Admin.</div>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-900">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-1.5"></i>
                    <b>Penting:</b> Pastikan HP toko tidak dalam mode <b>Do Not Disturb</b> dan notifikasi DANA tidak diblokir oleh pengaturan sistem HP. MacroDroid membutuhkan izin akses notifikasi — aktifkan saat pertama kali instal.
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
