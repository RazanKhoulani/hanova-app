<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductReviewService
{
    /**
     * Only customers with a delivered order can review a product. A reward is
     * issued for the first review only, so later edits cannot create coupons.
     *
     * @return array{review: ProductReview, coupon: ?Coupon, created: bool}
     */
    public function submit(User $user, Product $product, array $data): array
    {
        $hasDeliveredPurchase = $user->orders()
            ->where('status', 'delivered')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->exists();

        if (! $hasDeliveredPurchase) {
            throw ValidationException::withMessages([
                'product' => 'Only customers with a delivered order can review this product.',
            ]);
        }

        return DB::transaction(function () use ($user, $product, $data) {
            $review = ProductReview::query()->firstOrNew([
                'product_id' => $product->id,
                'user_id' => $user->id,
            ]);
            $created = ! $review->exists;

            $reviewData = [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ];
            if ($created) {
                $reviewData['is_visible'] = true;
            }

            $review->fill($reviewData);
            $review->save();

            $coupon = null;
            if ($created) {
                $coupon = $this->createReviewReward($user);
                if ($coupon) {
                    $review->update(['coupon_id' => $coupon->id]);
                }
            }

            return [
                'review' => $review->fresh('coupon'),
                'coupon' => $coupon,
                'created' => $created,
            ];
        });
    }

    private function createReviewReward(User $user): ?Coupon
    {
        $settings = AppSetting::reviewRewardValues();
        $discount = (float) $settings['review_reward_percentage'];
        if ($discount <= 0) {
            return null;
        }

        $expiresInDays = max(1, (int) $settings['review_reward_expiry_days']);

        do {
            $code = 'HANOVA-REVIEW-'.Str::upper(Str::random(8));
        } while (Coupon::query()->where('code', $code)->exists());

        return Coupon::create([
            'code' => $code,
            'user_id' => $user->id,
            'discount_type' => 'percentage',
            'discount_value' => $discount,
            'status' => 'active',
            'source' => 'product_review',
            'expires_at' => now()->addDays($expiresInDays),
        ]);
    }
}
