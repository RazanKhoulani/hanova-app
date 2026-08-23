<?php

namespace App\Models;

use Database\Factories\FaqTopicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaqTopic extends Model
{
    /** @use HasFactory<FaqTopicFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->orderBy('sort_order')->orderBy('id');
    }
}
