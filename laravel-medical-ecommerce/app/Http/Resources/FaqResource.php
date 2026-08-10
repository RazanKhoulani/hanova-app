<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->query('lang', $request->header('Accept-Language', 'ar'));
        $lang = str_starts_with((string) $lang, 'en') ? 'en' : 'ar';

        return [
            'id' => $this->id,
            'question_text' => $lang === 'en' ? $this->question_en : $this->question_ar,
            'answer_text' => $lang === 'en' ? $this->answer_en : $this->answer_ar,
            'question' => [
                'ar' => $this->question_ar,
                'en' => $this->question_en,
            ],
            'answer' => [
                'ar' => $this->answer_ar,
                'en' => $this->answer_en,
            ],
        ];
    }
}
