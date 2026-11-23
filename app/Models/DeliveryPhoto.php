<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'photo_path',
        'caption',
        'taken_at',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
