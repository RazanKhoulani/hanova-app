<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotMessage extends Model
{
    protected $fillable = [
        'bot_conversation_id',
        'sender',
        'body',
        'options',
        'metadata',
    ];

    protected $casts = [
        'options' => 'array',
        'metadata' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(BotConversation::class, 'bot_conversation_id');
    }
}
