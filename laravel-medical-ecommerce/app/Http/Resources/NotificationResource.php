<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->query('lang', $request->header('Accept-Language', 'ar'));
        $lang = str_starts_with((string) $lang, 'en') ? 'en' : 'ar';
        $translations = $this->data['translations'] ?? [];

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $translations[$lang]['title'] ?? $this->title,
            'body' => $translations[$lang]['body'] ?? $this->body,
            'type' => $this->type ?? 'general',
            'data' => $this->data,
            'is_read' => (bool) $this->is_read,
            'created_at' => $this->created_at,
        ];
    }
}
