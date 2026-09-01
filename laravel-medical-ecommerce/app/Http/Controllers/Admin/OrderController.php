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

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:pending,accepted,ready,paid,shipped,delivered,cancelled',
            'delivery_method' => 'nullable|in:clinic_pickup,pharmacy_pickup,home_delivery,qadmous',
        ]);
        $ordersQuery = Order::with(['user', 'items.product', 'deliveryArea', 'deliveryUser', 'coupon'])
            ->latest();

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $ordersQuery->where(function ($query) use ($search) {
                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search);
                }
                $query->orWhereHas('user', fn ($user) => $user
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        if (($filters['status'] ?? null) !== null) {
            $ordersQuery->where('status', $filters['status']);
        }
        if (($filters['delivery_method'] ?? null) !== null) {
            $ordersQuery->where('delivery_method', $filters['delivery_method']);
        }

        if (auth()->user()?->hasRole('delivery')) {
            $ordersQuery->where('delivery_user_id', auth()->id());
        }

        $orders = $ordersQuery->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders', 'filters'));
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

            return redirect()->back()->with('success', __('admin.order_marked_delivered'));
        }

        $request->validate([
            'status' => 'required|in:pending,accepted,ready,paid,shipped,delivered,cancelled',
        ]);

        $this->orderService->updateOrderStatus($id, $request->status);

        return redirect()->back()->with('success', __('admin.order_status_updated'));
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

        return redirect()->back()->with('success', __('admin.receipt_uploaded'));
    }

    public function updateTracking(Request $request, $id)
    {
        $validated = $request->validate(['tracking_number' => 'required|string|max:100']);
        Order::findOrFail($id)->update($validated);
        return redirect()->back()->with('success', __('admin.tracking_saved'));
    }
}
