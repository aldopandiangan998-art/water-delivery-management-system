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
}
