<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function getAllPaginated($perPage = 15)
    {
        return Product::with('concerns')
            ->withCount('visibleReviews')
            ->withAvg('visibleReviews as visible_reviews_avg_rating', 'rating')
            ->latest()
            ->paginate($perPage);
    }

    public function findById($id)
    {
        return Product::with('concerns')
            ->withCount('visibleReviews')
            ->withAvg('visibleReviews as visible_reviews_avg_rating', 'rating')
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data)
    {
        $product->update($data);
        return $product;
    }

    public function delete(Product $product)
    {
        return $product->delete();
    }
}
