<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;

class ProductReviewController extends Controller
{
    public function index()
    {
        $reviews = ProductReview::query()
            ->with([
                'product:id,name_ar,name_en',
                'user:id,name,phone',
                'coupon:id,code,discount_type,discount_value,status,expires_at',
            ])
            ->latest()
            ->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function toggleVisibility(ProductReview $review)
    {
        $review->update(['is_visible' => ! $review->is_visible]);

        return back()->with('success', $review->is_visible
            ? __('admin.review_now_visible')
            : __('admin.review_now_hidden'));
    }
}
