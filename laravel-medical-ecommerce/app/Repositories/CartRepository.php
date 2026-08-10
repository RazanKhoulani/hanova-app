<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;

class CartRepository
{
    public function getCartForUser($userId)
    {
        return Cart::with('items.product')->firstOrCreate(['user_id' => $userId]);
    }

    public function addOrUpdateItem(Cart $cart, $productId, $quantity)
    {
        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            $item->quantity = $quantity;
            $item->save();
        } else {
            $item = $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        return $item;
    }

    public function removeItem(Cart $cart, $itemId)
    {
        return $cart->items()->where('id', $itemId)->delete();
    }

    public function clearCart(Cart $cart)
    {
        return $cart->items()->delete();
    }
}
