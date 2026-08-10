<?php

namespace App\Services;

use App\Repositories\CartRepository;

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
        return $this->cartRepository->addOrUpdateItem($cart, $data['product_id'], $data['quantity']);
    }

    public function updateItemQuantity($userId, $itemId, $quantity)
    {
        $cart = $this->cartRepository->getCartForUser($userId);
        $item = $cart->items()->where('id', $itemId)->firstOrFail();
        
        $item->quantity = $quantity;
        $item->save();

        return $item;
    }

    public function removeItemFromCart($userId, $itemId)
    {
        $cart = $this->cartRepository->getCartForUser($userId);
        return $this->cartRepository->removeItem($cart, $itemId);
    }
}
