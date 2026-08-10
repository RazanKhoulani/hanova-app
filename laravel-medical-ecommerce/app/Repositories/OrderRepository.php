<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\User;

class OrderRepository
{
    public function getUserOrders($userId, $perPage = 15)
    {
        $user = User::find($userId);
        $query = Order::query();

        if ($user?->hasRole('delivery')) {
            $query->where('delivery_user_id', $userId);
        } else {
            $query->where('user_id', $userId);
        }

        return $query
            ->with(['items.product', 'deliveryArea', 'deliveryUser', 'coupon'])
            ->latest()
            ->paginate($perPage);
    }

    public function createOrder(array $data)
    {
        return Order::create($data);
    }

    public function createOrderItems(Order $order, array $items)
    {
        return $order->items()->createMany($items);
    }

    public function findById($id)
    {
        return Order::with(['items.product', 'deliveryArea', 'deliveryUser', 'coupon'])->findOrFail($id);
    }

    public function updateStatus(Order $order, $status)
    {
        $order->status = $status;
        $order->save();
        return $order;
    }

    public function update(Order $order, array $data)
    {
        $order->update($data);

        return $order->refresh();
    }
}
