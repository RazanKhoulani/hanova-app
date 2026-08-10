<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function getAllPaginated($perPage = 15)
    {
        return Product::with('concerns')->latest()->paginate($perPage);
    }

    public function findById($id)
    {
        return Product::with('concerns')->findOrFail($id);
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
