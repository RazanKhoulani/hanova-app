<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'message' => $this->body,
            'text' => $this->body,
            'type' => $this->type,
            'attachment' => $this->attachment ? Storage::url($this->attachment) : null,
            'file_url' => $this->attachment ? Storage::url($this->attachment) : null,
            'is_me' => $request->user() ? $request->user()->id === $this->sender_id : false,
            'created_at' => $this->created_at,
        ];
    }
}
