<?php

namespace App\Models;

use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    protected $fillable = [
        'faq_topic_id',
        'question_ar',
        'question_en',
        'answer_ar',
        'answer_en',
        'keywords',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(FaqTopic::class, 'faq_topic_id');
    }
}
