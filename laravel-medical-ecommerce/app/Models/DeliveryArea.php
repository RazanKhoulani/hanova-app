<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'fee',
        'fee_usd',
        'is_active',
    ];

    protected $casts = [
        'fee' => 'float',
        'fee_usd' => 'float',
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
