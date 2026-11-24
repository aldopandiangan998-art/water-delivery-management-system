<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_address_id',
        'recipient_name',
        'recipient_phone',
        'delivery_address_text',
        'delivery_benchmark',
        'delivery_latitude',
        'delivery_longitude',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'delivery_fee',
        'discount',
        'total_amount',
        'empty_gallons_returned',
        'customer_notes',
        'admin_notes',
        'cancellation_reason',
        'cancelled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'empty_gallons_returned' => 'integer',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'customer_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending' => ['label' => 'Menunggu Konfirmasi', 'color' => 'bg-amber-100 text-amber-800 border-amber-200'],
            'confirmed' => ['label' => 'Terkonfirmasi', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
            'processing' => ['label' => 'Diproses', 'color' => 'bg-indigo-100 text-indigo-800 border-indigo-200'],
            'ready_for_delivery' => ['label' => 'Siap Dikirim', 'color' => 'bg-cyan-100 text-cyan-800 border-cyan-200'],
            'completed' => ['label' => 'Selesai', 'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => 'bg-gray-100 text-gray-800 border-gray-200'],
            'rejected' => ['label' => 'Ditolak', 'color' => 'bg-rose-100 text-rose-800 border-rose-200'],
            default => ['label' => $this->status, 'color' => 'bg-gray-100 text-gray-800 border-gray-200'],
        };
    }

    public function getPaymentBadgeAttribute(): array
    {
        return match ($this->payment_status) {
            'unpaid' => ['label' => 'Belum Lunas', 'color' => 'bg-rose-100 text-rose-800 border-rose-200'],
            'partially_paid' => ['label' => 'Bayar Sebagian', 'color' => 'bg-amber-100 text-amber-800 border-amber-200'],
            'paid' => ['label' => 'Lunas', 'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
            'refunded' => ['label' => 'Refund', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
            default => ['label' => $this->payment_status, 'color' => 'bg-gray-100 text-gray-800 border-gray-200'],
        };
    }

    public function getPaymentMethodBadgeAttribute(): array
    {
        return match ($this->payment_method) {
            'cod' => ['label' => 'Tunai / COD', 'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'icon' => 'fa-hand-holding-dollar'],
            'qris' => ['label' => 'QRIS (Doppnnstore)', 'color' => 'bg-teal-100 text-teal-800 border-teal-200', 'icon' => 'fa-qrcode'],
            default => ['label' => strtoupper(str_replace('_', ' ', $this->payment_method)), 'color' => 'bg-slate-100 text-slate-800 border-slate-200', 'icon' => 'fa-credit-card'],
        };
    }

    public function getWhatsappUrlAttribute(): string
    {
        $adminPhone = BusinessSetting::get('company_whatsapp', '62881026146848');
        $cleanPhone = preg_replace('/[^0-9]/', '', $adminPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $itemsText = "";
        foreach ($this->items as $item) {
            $exchangeNote = $item->is_exchange ? " (Tukar Galon)" : "";
            $itemsText .= "• {$item->quantity}x {$item->product_name}{$exchangeNote} - Rp " . number_format($item->subtotal, 0, ',', '.') . "\n";
        }

        $mapLink = "";
        if ($this->delivery_latitude && $this->delivery_longitude) {
            $mapLink = "\n📍 Link Maps: https://maps.google.com/?q={$this->delivery_latitude},{$this->delivery_longitude}";
        }

        $notesText = $this->customer_notes ? "\n📝 Catatan: {$this->customer_notes}" : "";
        $benchmarkText = $this->delivery_benchmark ? "\n🧭 Patokan: {$this->delivery_benchmark}" : "";
        
        $paymentLabel = match ($this->payment_method) {
            'qris' => "QRIS (Scan & Pay - Doppnnstore)\n(Bukti screenshot transfer QRIS saya kirimkan di bawah ini)",
            'cod' => "Tunai / COD (Bayar di Tempat saat galon sampai)",
            default => strtoupper($this->payment_method),
        };

        $message = "Halo Admin AquaFlow (Toko Nova Jaya), saya mau konfirmasi pesanan baru:\n\n"
            . "📌 No. Pesanan: #{$this->order_number}\n"
            . "👤 Penerima: {$this->recipient_name} ({$this->recipient_phone})\n"
            . "🏠 Alamat: {$this->delivery_address_text}{$benchmarkText}{$mapLink}\n\n"
            . "📦 Rincian Pesanan:\n"
            . $itemsText
            . "🚚 Ongkir: Rp " . number_format($this->delivery_fee, 0, ',', '.') . "\n"
            . "💰 Total Tagihan: Rp " . number_format($this->total_amount, 0, ',', '.') . "\n"
            . "💳 Metode Pembayaran: {$paymentLabel}"
            . $notesText . "\n\n"
            . "Mohon segera diproses dan diantar ya min, terima kasih!";

        return "https://wa.me/{$cleanPhone}?text=" . rawurlencode($message);
    }
}
