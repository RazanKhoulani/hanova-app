<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

        $previousImage = $product->image;

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('products', 'public');
        }

        $updatedProduct = $this->productRepository->update($product, $data);

        if (isset($data['image']) && $data['image'] !== $previousImage) {
            $this->deleteStoredImage($previousImage);
        }

        if (is_array($concernIds)) {
            $updatedProduct->concerns()->sync($concernIds);
        }

        return $updatedProduct->load('concerns');
    }

    public function deleteProduct($product)
    {
        $this->deleteStoredImage($product->image);

        return $this->productRepository->delete($product);
    }

    private function deleteStoredImage(?string $image): void
    {
        if (!$image || str_starts_with($image, 'http')) {
            return;
        }

        Storage::disk('public')->delete($image);
    }
}
