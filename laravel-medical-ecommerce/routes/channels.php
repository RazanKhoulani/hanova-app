<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    if ($user->hasRole('admin') || $user->hasRole('doctor')) {
        return true;
    }

    return Conversation::query()
        ->where('id', $conversationId)
        ->where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('doctor_id', $user->id);
        })
        ->exists();
});
