<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'price',
        'cost',
        'image',
        'category',
        'brand',
        'catalog_type',
        'bundle_product_ids',
        'usage_ar', 'usage_en',
        'suitable_for_ar', 'suitable_for_en',
        'active_ingredients_ar', 'active_ingredients_en',
        'warnings_ar', 'warnings_en',
    ];

    protected $casts = [
        'bundle_product_ids' => 'array',
    ];

    public function concerns(): BelongsToMany
    {
        return $this->belongsToMany(Concern::class)->withTimestamps();
    }
}
