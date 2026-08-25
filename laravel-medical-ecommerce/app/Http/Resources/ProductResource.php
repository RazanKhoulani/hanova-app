<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->query('lang', $request->header('Accept-Language', 'ar'));
        $lang = str_starts_with((string) $lang, 'en') ? 'en' : 'ar';
        $name = $lang === 'en' ? $this->name_en : $this->name_ar;
        $description = $lang === 'en' ? $this->description_en : $this->description_ar;
        $imageUrl = $this->image ? Storage::disk('public')->url($this->image) : null;
        if ($imageUrl && ! str_starts_with($imageUrl, 'http')) {
            $imageUrl = url($imageUrl);
        }
        if ($imageUrl) {
            $separator = str_contains($imageUrl, '?') ? '&' : '?';
            $imageUrl .= $separator.'v='.($this->updated_at?->timestamp ?? $this->id);
        }

        return [
            'id' => $this->id,
            'name' => $name,
            'name_translations' => [
                'ar' => $this->name_ar,
                'en' => $this->name_en,
            ],
            'description' => $description,
            'description_translations' => [
                'ar' => $this->description_ar,
                'en' => $this->description_en,
            ],
            'usage' => $lang === 'en' ? $this->usage_en : $this->usage_ar,
            'suitable_for' => $lang === 'en' ? $this->suitable_for_en : $this->suitable_for_ar,
            'active_ingredients' => $lang === 'en' ? $this->active_ingredients_en : $this->active_ingredients_ar,
            'warnings' => $lang === 'en' ? $this->warnings_en : $this->warnings_ar,
            'bot_details' => [
                'usage' => ['ar' => $this->usage_ar, 'en' => $this->usage_en],
                'suitable_for' => ['ar' => $this->suitable_for_ar, 'en' => $this->suitable_for_en],
                'active_ingredients' => ['ar' => $this->active_ingredients_ar, 'en' => $this->active_ingredients_en],
                'warnings' => ['ar' => $this->warnings_ar, 'en' => $this->warnings_en],
            ],
            'price' => (float) $this->price,
            'currency_code' => config('app.currency_code', 'SYP'),
            'currency_symbol' => config('app.currency_symbol', 'ل.س'),
            'category' => $this->category,
            'brand' => $this->brand,
            'catalog_type' => $this->catalog_type ?? 'product',
            'bundle_product_ids' => $this->bundle_product_ids ?? [],
            'rating_average' => round((float) ($this->visible_reviews_avg_rating ?? 0), 1),
            'rating_count' => (int) ($this->visible_reviews_count ?? 0),
            'can_review' => (bool) ($this->can_review ?? false),
            'current_user_review' => $this->when(
                $this->relationLoaded('currentUserReview') && $this->currentUserReview,
                fn () => [
                    'id' => $this->currentUserReview->id,
                    'rating' => $this->currentUserReview->rating,
                    'comment' => $this->currentUserReview->comment,
                    'created_at' => $this->currentUserReview->created_at,
                ],
            ),
            'concerns' => $this->whenLoaded('concerns', function () use ($lang) {
                return $this->concerns->map(fn ($concern) => [
                    'id' => $concern->id,
                    'name' => $lang === 'en' ? $concern->name_en : $concern->name_ar,
                    'slug' => $concern->slug,
                    'image' => $concern->image,
                ])->values();
            }),
            'image' => $imageUrl,
            'image_url' => $imageUrl,
            'created_at' => $this->created_at,
        ];
    }
}
