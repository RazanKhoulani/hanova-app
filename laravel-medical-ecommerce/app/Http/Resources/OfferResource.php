<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->query('lang', $request->header('Accept-Language', 'ar'));
        $lang = str_starts_with((string) $lang, 'en') ? 'en' : 'ar';

        return [
            'id' => $this->id,
            'title' => $lang === 'en' ? $this->title_en : $this->title_ar,
            'title_translations' => [
                'ar' => $this->title_ar,
                'en' => $this->title_en,
            ],
            'description' => $lang === 'en' ? $this->description_en : $this->description_ar,
            'description_translations' => [
                'ar' => $this->description_ar,
                'en' => $this->description_en,
            ],
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'target_segment' => $this->target_segment,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'image' => $this->image ? Storage::url($this->image) : null,
        ];
    }
}
