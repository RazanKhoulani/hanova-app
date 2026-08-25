<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductReviewService;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product, ProductReviewService $reviewService)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $reviewService->submit($request->user(), $product, $data);
        $coupon = $result['coupon'];

        return response()->json([
            'message' => $result['created'] ? 'Review submitted successfully.' : 'Review updated successfully.',
            'data' => [
                'review' => [
                    'id' => $result['review']->id,
                    'rating' => $result['review']->rating,
                    'comment' => $result['review']->comment,
                    'created_at' => $result['review']->created_at,
                ],
                'reward_coupon' => $coupon ? [
                    'code' => $coupon->code,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => (float) $coupon->discount_value,
                    'expires_at' => $coupon->expires_at,
                ] : null,
            ],
        ], $result['created'] ? 201 : 200);
    }
}
