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
    ];

    public function concerns(): BelongsToMany
    {
        return $this->belongsToMany(Concern::class)->withTimestamps();
    }
}
