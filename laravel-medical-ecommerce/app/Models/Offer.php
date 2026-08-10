<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'discount_type',
        'discount_value',
        'target_segment',
        'starts_at',
        'ends_at',
        'priority',
        'image',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeRunning(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }
}
