<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'price',
        'price_syp',
        'price_usd',
        'cost',
        'cost_syp',
        'cost_usd',
        'image',
        'category',
        'brand',
        'catalog_type',
        'bundle_product_ids',
        'track_inventory',
        'stock_quantity',
        'low_stock_threshold',
        'usage_ar', 'usage_en',
        'suitable_for_ar', 'suitable_for_en',
        'active_ingredients_ar', 'active_ingredients_en',
        'warnings_ar', 'warnings_en',
    ];

    protected $casts = [
        'price_syp' => 'decimal:2',
        'price_usd' => 'decimal:2',
        'cost_syp' => 'decimal:2',
        'cost_usd' => 'decimal:2',
        'bundle_product_ids' => 'array',
        'track_inventory' => 'boolean',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function concerns(): BelongsToMany
    {
        return $this->belongsToMany(Concern::class)->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function visibleReviews(): HasMany
    {
        return $this->reviews()->where('is_visible', true);
    }
}
