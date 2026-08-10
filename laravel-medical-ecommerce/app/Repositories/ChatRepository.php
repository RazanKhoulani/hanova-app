<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Models\Message;

class ChatRepository
{
    public function getUserConversations($userId, $perPage = 15)
    {
        return Conversation::where('user_id', $userId)
            ->orWhere('doctor_id', $userId)
            ->with(['messages' => function($q) {
                $q->latest()->limit(1); // load last message
            }])->latest()->paginate($perPage);
    }

    public function getConversationMessages($conversationId, $perPage = 50)
    {
        return Message::where('conversation_id', $conversationId)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrCreateConversation($userId, $doctorId)
    {
        return Conversation::firstOrCreate([
            'user_id' => $userId,
            'doctor_id' => $doctorId
        ]);
    }

    public function createMessage(array $data)
    {
        return Message::create($data);
    }

    public function findConversationById($id)
    {
        return Conversation::findOrFail($id);
    }
}
