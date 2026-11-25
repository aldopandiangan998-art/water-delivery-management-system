<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_number',
        'order_id',
        'status',
        'started_at',
        'arrived_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'arrived_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(DeliveryStatusHistory::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(DeliveryLocation::class)->orderBy('recorded_at', 'desc');
    }

    public function latestLocation(): HasOne
    {
        return $this->hasOne(DeliveryLocation::class)->latestOfMany('recorded_at');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DeliveryPhoto::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'not_assigned' => ['label' => 'Menunggu Pengantaran', 'color' => 'bg-gray-100 text-gray-800 border-gray-200'],
            'assigned' => ['label' => 'Siap Dikirim', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
            'in_transit' => ['label' => 'Dalam Perjalanan', 'color' => 'bg-amber-100 text-amber-800 border-amber-200 animate-pulse'],
            'arrived' => ['label' => 'Tiba di Lokasi', 'color' => 'bg-indigo-100 text-indigo-800 border-indigo-200'],
            'delivered' => ['label' => 'Terkirim', 'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
            'failed' => ['label' => 'Gagal', 'color' => 'bg-rose-100 text-rose-800 border-rose-200'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => 'bg-gray-100 text-gray-800 border-gray-200'],
            default => ['label' => $this->status, 'color' => 'bg-gray-100 text-gray-800 border-gray-200'],
        };
    }
}
