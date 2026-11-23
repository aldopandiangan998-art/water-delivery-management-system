<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GallonMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'gallons_delivered',
        'empty_gallons_returned',
        'balance_after',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'gallons_delivered' => 'integer',
            'empty_gallons_returned' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
