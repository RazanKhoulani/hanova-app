<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $ordersQuery = Order::with(['user', 'items.product', 'deliveryArea', 'deliveryUser', 'coupon'])
            ->latest();

        if (auth()->user()?->hasRole('delivery')) {
            $ordersQuery->where('delivery_user_id', auth()->id());
        }

        $orders = $ordersQuery->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $orderQuery = Order::with(['user', 'items.product', 'deliveryArea', 'deliveryUser', 'coupon']);

        if (auth()->user()?->hasRole('delivery')) {
            $orderQuery->where('delivery_user_id', auth()->id());
        }

        $order = $orderQuery->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        if (auth()->user()?->hasRole('delivery')) {
            $request->validate([
                'status' => 'required|in:delivered',
            ]);

            Order::query()
                ->where('delivery_user_id', auth()->id())
                ->findOrFail($id);

            $this->orderService->updateOrderStatus($id, 'delivered');

            return redirect()->back()->with('success', 'Order marked as delivered successfully');
        }

        $request->validate([
            'status' => 'required|in:pending,accepted,ready,paid,shipped,delivered,cancelled',
        ]);

        $this->orderService->updateOrderStatus($id, $request->status);

        return redirect()->back()->with('success', 'Order status updated successfully');
    }

    public function uploadReceipt(Request $request, $id)
    {
        if (auth()->user()?->hasRole('delivery')) {
            abort(403);
        }

        $request->validate([
            'shipping_receipt' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('shipping_receipt')) {
            $path = $request->file('shipping_receipt')->store('receipts', 'public');
            $this->orderService->updateShippingReceipt($id, $path);
        }

        return redirect()->back()->with('success', 'Shipping receipt uploaded successfully');
    }
}
