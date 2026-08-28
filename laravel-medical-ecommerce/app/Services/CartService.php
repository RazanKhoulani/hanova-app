<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\CartRepository;
use Illuminate\Validation\ValidationException;

class CartService
{
    protected CartRepository $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function getUserCart($userId)
    {
        return $this->cartRepository->getCartForUser($userId);
    }

    public function addItemToCart($userId, array $data)
    {
        $cart = $this->cartRepository->getCartForUser($userId);
        $product = Product::findOrFail($data['product_id']);
        $existingQuantity = (int) $cart->items()
            ->where('product_id', $product->id)
            ->value('quantity');
        $requestedQuantity = $existingQuantity + (int) $data['quantity'];

        $this->ensureStockAvailable($product, $requestedQuantity);

        return $this->cartRepository->addOrUpdateItem($cart, $data['product_id'], $requestedQuantity);
    }

    public function updateItemQuantity($userId, $itemId, $quantity)
    {
        $cart = $this->cartRepository->getCartForUser($userId);
        $item = $cart->items()->where('id', $itemId)->firstOrFail();
        $item->load('product');
        $this->ensureStockAvailable($item->product, $quantity);
        
        $item->quantity = $quantity;
        $item->save();

        return $item;
    }

    public function removeItemFromCart($userId, $itemId)
    {
        $cart = $this->cartRepository->getCartForUser($userId);
        return $this->cartRepository->removeItem($cart, $itemId);
    }

    private function ensureStockAvailable(Product $product, int $quantity): void
    {
        if (! $product->track_inventory || $product->stock_quantity >= $quantity) {
            return;
        }

        throw ValidationException::withMessages([
            'quantity' => "Only {$product->stock_quantity} units of {$product->name_en} are available.",
        ]);
    }
}
