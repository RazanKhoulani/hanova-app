<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'discount_type',
        'discount_value',
        'status',
        'source',
        'expires_at',
        'used_at',
        'used_order_id',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function usedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'used_order_id');
    }
}
