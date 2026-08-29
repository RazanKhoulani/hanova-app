<?php

namespace App\Http\Resources;

use App\Models\Product;
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

        $catalogType = $this->catalog_type ?? 'product';
        $stock = (int) ($this->stock_quantity ?? 0);
        $tracksInventory = (bool) ($this->track_inventory ?? true);
        $isInStock = ! $tracksInventory || $stock > 0;
        $isLowStock = $tracksInventory
            && $stock > 0
            && $stock <= (int) ($this->low_stock_threshold ?? 5);

        if ($catalogType === 'bundle' && ! empty($this->bundle_product_ids)) {
            $componentIds = collect($this->bundle_product_ids)
                ->map(fn ($productId) => (int) $productId)
                ->filter()
                ->unique()
                ->values();
            $components = Product::query()
                ->whereIn('id', $componentIds)
                ->get(['id', 'track_inventory', 'stock_quantity', 'low_stock_threshold']);
            $trackedComponents = $components->filter(fn (Product $component) => $component->track_inventory);

            $tracksInventory = $trackedComponents->isNotEmpty();
            $stock = $trackedComponents->isEmpty()
                ? 0
                : (int) $trackedComponents->min('stock_quantity');
            $isInStock = $components->count() === $componentIds->count()
                && $components->every(fn (Product $component) =>
                    ! $component->track_inventory || (int) $component->stock_quantity > 0);
            $isLowStock = $isInStock
                && $trackedComponents->contains(fn (Product $component) =>
                    (int) $component->stock_quantity <= (int) ($component->low_stock_threshold ?? 5));
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
            'catalog_type' => $catalogType,
            'bundle_product_ids' => $this->bundle_product_ids ?? [],
            'stock' => $stock,
            'tracks_inventory' => $tracksInventory,
            'is_in_stock' => $isInStock,
            'is_low_stock' => $isLowStock,
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
