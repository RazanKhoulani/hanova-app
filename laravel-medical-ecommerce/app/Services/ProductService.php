<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Http\UploadedFile;

class ProductService
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts($perPage = 15)
    {
        return $this->productRepository->getAllPaginated($perPage);
    }

    public function getProductById($id)
    {
        return $this->productRepository->findById($id);
    }

    public function createProduct(array $data)
    {
        $concernIds = $data['concern_ids'] ?? [];
        unset($data['concern_ids']);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('products', 'public');
        }

        $product = $this->productRepository->create($data);
        $product->concerns()->sync($concernIds);

        return $product->load('concerns');
    }

    public function updateProduct($product, array $data)
    {
        $concernIds = $data['concern_ids'] ?? null;
        unset($data['concern_ids']);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('products', 'public');
        }

        $updatedProduct = $this->productRepository->update($product, $data);

        if (is_array($concernIds)) {
            $updatedProduct->concerns()->sync($concernIds);
        }

        return $updatedProduct->load('concerns');
    }

    public function deleteProduct($product)
    {
        return $this->productRepository->delete($product);
    }
}
