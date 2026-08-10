<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Resources\CartItemResource;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get current user's cart
     */
    public function index(Request $request)
    {
        $cart = $this->cartService->getUserCart($request->user()->id);
        // Assuming getUserCart returns a collection of items or a cart object with items
        return CartItemResource::collection($cart->items);
    }

    /**
     * Add item to cart
     */
    public function store(AddToCartRequest $request)
    {
        $item = $this->cartService->addItemToCart($request->user()->id, $request->validated());
        return new CartItemResource($item->load('product'));
    }

    /**
     * Update item quantity in cart
     */
    public function update(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = $this->cartService->updateItemQuantity($request->user()->id, $itemId, $request->quantity);

        return new CartItemResource($item->load('product'));
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, $itemId)
    {
        $this->cartService->removeItemFromCart($request->user()->id, $itemId);

        return response()->json(['message' => 'Item removed from cart successfully'], 204);
    }
}
