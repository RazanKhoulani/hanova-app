<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\ProtectedFileUrl;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender?->name,
            'message' => $this->body,
            'text' => $this->body,
            'type' => $this->type,
            'attachment' => $this->attachment ? ProtectedFileUrl::make('message', $this->id, 'attachment', $request->user()?->id) : null,
            'file_url' => $this->attachment ? ProtectedFileUrl::make('message', $this->id, 'attachment', $request->user()?->id) : null,
            'is_me' => $request->user() ? $request->user()->id === $this->sender_id : false,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at,
        ];
    }
}
