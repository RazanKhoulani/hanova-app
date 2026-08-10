<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Http\Requests\Order\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of user orders.
     */
    public function index()
    {
        $orders = $this->orderService->getUserOrders(auth()->id());
        return OrderResource::collection($orders);
    }

    /**
     * Place an order (checkout).
     */
    public function store(CheckoutRequest $request)
    {
        $order = $this->orderService->checkout(auth()->id(), $request->validated());

        return (new OrderResource(
            $order->load(['items.product', 'deliveryArea', 'deliveryUser', 'coupon'])
        ))->response()->setStatusCode(201);
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $order = $this->orderService->getOrderById($id);
        $this->authorizeOrderAccess($order, request()->user());

        return new OrderResource($order->load(['items.product', 'deliveryArea', 'deliveryUser', 'coupon']));
    }

    public function confirm(Request $request, $id)
    {
        if (!$this->isStaff($request->user())) {
            abort(403);
        }

        $this->orderService->confirmOrder($id);
        return response()->json(['message' => 'Order confirmed successfully']);
    }

    public function markDelivered(Request $request, $id)
    {
        $user = $request->user();

        if (
            !$user->hasRole('admin')
            && !$user->hasRole('doctor')
            && !$user->hasRole('delivery')
        ) {
            abort(403);
        }

        if ($user->hasRole('delivery')) {
            Order::query()
                ->where('delivery_user_id', $user->id)
                ->findOrFail($id);
        }

        $order = $this->orderService->updateOrderStatus($id, 'delivered');

        return new OrderResource($order->load(['items.product', 'deliveryArea', 'deliveryUser', 'coupon']));
    }

    private function authorizeOrderAccess(Order $order, $user): void
    {
        if ($this->isStaff($user)) {
            return;
        }

        if ($user?->hasRole('delivery') && (int) $order->delivery_user_id === (int) $user->id) {
            return;
        }

        if ((int) $order->user_id !== (int) $user->id) {
            abort(403);
        }
    }

    private function isStaff($user): bool
    {
        return $user?->hasRole('admin') || $user?->hasRole('doctor');
    }
}
