<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'district',
        'postal_code',
        'delivery_fee',
        'min_order_amount',
        'estimated_delivery_time',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'delivery_fee' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }
}
