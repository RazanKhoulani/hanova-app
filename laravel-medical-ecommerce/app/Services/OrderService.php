<?php

namespace App\Services;

use App\Models\DeliveryArea;
use App\Models\Coupon;
use App\Models\Notification;
use App\Models\InventoryMovement;
use App\Models\QadmousLocation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OrderService
{
    protected OrderRepository $orderRepository;
    protected CartRepository $cartRepository;

    public function __construct(
        OrderRepository $orderRepository,
        CartRepository $cartRepository,
        private readonly OfferService $offerService,
        private readonly AuditService $auditService,
    )
    {
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
    }

    public function getUserOrders($userId)
    {
        return $this->orderRepository->getUserOrders($userId);
    }

    public function getOrderById($id)
    {
        return $this->orderRepository->findById($id);
    }

    public function checkout($userId, array $data)
    {
        DB::beginTransaction();

        try {
            $subtotal = 0;
            $subtotalUsd = 0.0;
            $hasCompleteUsdPricing = true;
            $orderItems = [];
            $usesClientItems = !empty($data['items']) && is_array($data['items']);

            if ($usesClientItems) {
                foreach ($data['items'] as $item) {
                    $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);
                    $quantity = (int) $item['quantity'];
                    $price = (float) $product->price;

                    $this->assertInventoryAvailable($product, $quantity);

                    $subtotal += $price * $quantity;
                    if ($product->price_usd === null) {
                        $hasCompleteUsdPricing = false;
                    } else {
                        $subtotalUsd += (float) $product->price_usd * $quantity;
                    }
                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'price_usd' => $product->price_usd,
                    ];
                }
            } else {
                $cart = $this->cartRepository->getCartForUser($userId);

                if ($cart->items->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => 'Cart is empty.',
                    ]);
                }

                foreach ($cart->items as $item) {
                    $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);
                    $price = (float) $product->price;
                    $quantity = (int) $item->quantity;

                    $this->assertInventoryAvailable($product, $quantity);

                    $subtotal += $price * $quantity;
                    if ($product->price_usd === null) {
                        $hasCompleteUsdPricing = false;
                    } else {
                        $subtotalUsd += (float) $product->price_usd * $quantity;
                    }
                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'price_usd' => $product->price_usd,
                    ];
                }
            }

            $deliveryMethod = $this->normalizeDeliveryMethod($data['delivery_method'] ?? null);
            [$deliveryFee, $deliveryFeeUsd] = $this->resolveDeliveryFee($deliveryMethod, $data['delivery_area_id'] ?? null);
            $deliveryUserId = $deliveryMethod === 'home_delivery'
                ? $this->resolveDefaultDeliveryUserId()
                : null;
            $subtotalUsd = $hasCompleteUsdPricing ? round($subtotalUsd, 2) : null;
            [$coupon, $discountAmount, $discountAmountUsd] = $this->resolveNextOrderCoupon($userId, $subtotal, $subtotalUsd);
            $appliedOffer = null;
            if (! $coupon) {
                [$appliedOffer, $discountAmount, $discountAmountUsd] = $this->resolveActiveOfferDiscount($userId, $subtotal, $subtotalUsd);
            }

            $paymentMethod = $data['payment_method'] ?? 'cash';
            $orderData = [
                'user_id' => $userId,
                'status' => 'pending',
                'shipping_address' => $data['shipping_address'] ?? null,
                'shipping_latitude' => $data['shipping_latitude'] ?? null,
                'shipping_longitude' => $data['shipping_longitude'] ?? null,
                'shipping_receipt' => $data['shipping_receipt'] ?? null,
                'receipt_disk' => $data['receipt_disk'] ?? null,
                'payment_method' => $paymentMethod,
                'subtotal_amount' => $subtotal,
                'subtotal_usd' => $subtotalUsd,
                'discount_amount_usd' => $discountAmountUsd,
                'delivery_fee_usd' => $deliveryFeeUsd,
                'total_amount' => max(0, $subtotal - $discountAmount) + $deliveryFee,
                'total_amount_usd' => $subtotalUsd !== null && $deliveryFeeUsd !== null && $discountAmountUsd !== null
                    ? max(0, $subtotalUsd - $discountAmountUsd) + $deliveryFeeUsd
                    : null,
            ];

            if (Schema::hasColumn('orders', 'coupon_id')) {
                $orderData['coupon_id'] = $coupon?->id;
            }

            if (Schema::hasColumn('orders', 'applied_offer_id')) {
                $orderData['applied_offer_id'] = $appliedOffer?->id;
            }

            if (Schema::hasColumn('orders', 'discount_amount')) {
                $orderData['discount_amount'] = $discountAmount;
            }

            if (Schema::hasColumn('orders', 'payment_status')) {
                $orderData['payment_status'] = in_array($paymentMethod, ['cash', 'cash_on_delivery'], true)
                    ? 'unpaid'
                    : 'pending';
            }

            if (Schema::hasColumn('orders', 'payment_receipt_status')) {
                $orderData['payment_receipt_status'] = in_array($paymentMethod, ['cash', 'cash_on_delivery'], true)
                    ? 'not_required'
                    : (! empty($data['shipping_receipt']) ? 'pending' : 'missing');
            }

            if (Schema::hasColumn('orders', 'delivery_method')) {
                $orderData['delivery_method'] = $deliveryMethod;
                $orderData['pickup_location'] = match ($deliveryMethod) {
                    'clinic_pickup' => 'clinic',
                    'pharmacy_pickup' => 'pharmacy',
                    default => null,
                };
                $orderData['delivery_area_id'] = $deliveryMethod === 'home_delivery'
                    ? ($data['delivery_area_id'] ?? null)
                    : null;
                $orderData['delivery_fee'] = $deliveryFee;
                $orderData['delivery_user_id'] = $deliveryUserId;
            }
            if ($deliveryMethod === 'qadmous') {
                $qadmousLocation = QadmousLocation::query()
                    ->where('is_active', true)
                    ->findOrFail($data['qadmous_location_id']);
                $qadmousFields = [
                    'qadmous_location_id' => $qadmousLocation->id,
                    'qadmous_governorate' => $qadmousLocation->governorate_ar,
                    'qadmous_branch' => $qadmousLocation->branch_ar,
                    'recipient_name' => $data['recipient_name'],
                    'recipient_phone' => $data['recipient_phone'],
                ];

                foreach ($qadmousFields as $column => $value) {
                    if (Schema::hasColumn('orders', $column)) {
                        $orderData[$column] = $value;
                    }
                }

                $orderData['shipping_address'] = $qadmousLocation->governorate_ar.' - '.$qadmousLocation->branch_ar;
            }

            $order = $this->orderRepository->createOrder($orderData);
            $this->orderRepository->createOrderItems($order, $orderItems);

            if ($coupon) {
                $coupon->update([
                    'status' => 'used',
                    'used_at' => now(),
                    'used_order_id' => $order->id,
                ]);
            }

            $this->createOrderNotification($order, 'order_created');
            foreach (User::role('admin')->pluck('id') as $staffId) {
                Notification::create([
                    'user_id' => $staffId,
                    'title' => 'طلب جديد',
                    'body' => "تم استلام طلب جديد رقم #{$order->id} بقيمة {$order->total_amount} ل.س.",
                    'type' => 'new_order',
                    'data' => ['order_id' => $order->id],
                ]);
            }

            // Checkout always represents one unified order, so clear the
            // authenticated server cart even when the app sent its item list.
            $cart = $this->cartRepository->getCartForUser($userId);
            $this->cartRepository->clearCart($cart);

            DB::commit();

            return $order->load(['items', 'coupon', 'appliedOffer']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateOrderStatus($id, $status)
    {
        return DB::transaction(function () use ($id, $status) {
            $order = Order::query()->with('items')->lockForUpdate()->findOrFail($id);
            $previousStatus = $order->status;

            if ($status === 'delivered' && !in_array($order->status, ['accepted', 'ready', 'shipped'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only an accepted, ready, or shipped order can be marked as delivered.',
                ]);
            }

            $updateData = ['status' => $status];

            if (in_array($status, ['accepted', 'paid', 'ready', 'shipped', 'delivered'], true)) {
                if (! in_array($order->payment_method, ['cash', 'cash_on_delivery'], true)
                    && $order->payment_receipt_status !== 'approved') {
                    throw ValidationException::withMessages([
                        'payment_receipt_status' => 'Approve the payment receipt before processing this order.',
                    ]);
                }

                $this->reserveOrderInventory($order);
            }

            if ($status === 'delivered') {
                if (Schema::hasColumn('orders', 'payment_status')) {
                    if (in_array($order->payment_method, ['cash', 'cash_on_delivery'], true)) {
                        $updateData['payment_status'] = 'paid';
                    } elseif ($order->payment_status !== 'paid') {
                        throw ValidationException::withMessages([
                            'payment_status' => 'A prepaid order must have an approved receipt before delivery.',
                        ]);
                    }
                }
            }

            if ($status === 'paid' && Schema::hasColumn('orders', 'payment_status')) {
                if (! in_array($order->payment_method, ['cash', 'cash_on_delivery'], true)
                    && $order->payment_receipt_status !== 'approved') {
                    throw ValidationException::withMessages([
                        'payment_status' => 'Approve the payment receipt before marking this order as paid.',
                    ]);
                }
                $updateData['payment_status'] = 'paid';
            }

            $updatedOrder = $this->orderRepository->update($order, $updateData);
            if ($status === 'cancelled'
                && ! in_array($previousStatus, ['delivered', 'cancelled'], true)) {
                $this->releaseInventory($updatedOrder);
            }
            $this->createOrderNotification($updatedOrder, $this->notificationTypeForStatus($status));
            $this->auditService->record('order.status_updated', $updatedOrder, [
                'from' => $previousStatus,
                'to' => $status,
                'payment_method' => $updatedOrder->payment_method,
                'payment_status' => $updatedOrder->payment_status,
            ]);

            return $updatedOrder;
        });
    }

    public function confirmOrder($id)
    {
        return DB::transaction(function () use ($id) {
            $order = Order::query()->with('items')->lockForUpdate()->findOrFail($id);

            if (in_array($order->status, ['cancelled', 'delivered'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This order can no longer be confirmed.',
                ]);
            }

            if (! in_array($order->payment_method, ['cash', 'cash_on_delivery'], true)
                && $order->payment_receipt_status !== 'approved') {
                throw ValidationException::withMessages([
                    'payment_receipt_status' => 'The payment receipt must be approved before accepting this order.',
                ]);
            }

            if ($order->status === 'accepted' && $order->is_confirmed && $order->inventory_reserved_at) {
                return true;
            }

            $this->reserveOrderInventory($order);
            $updated = $order->update(['is_confirmed' => true, 'status' => 'accepted']);
            $this->createOrderNotification($order->refresh(), 'order_accepted');

            return $updated;
        });
    }

    public function updateShippingReceipt($id, $path)
    {
        $order = $this->orderRepository->findById($id);

        return $order->update([
            'shipping_receipt' => $path,
            'receipt_disk' => config('filesystems.medical_disk', 'local'),
            'payment_receipt_status' => 'pending',
            'payment_status' => 'pending',
            'receipt_reviewed_by' => null,
            'receipt_reviewed_at' => null,
            'receipt_rejection_reason' => null,
        ]);
    }

    public function reviewPaymentReceipt(int $id, bool $approved, int $reviewerId, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($id, $approved, $reviewerId, $reason) {
            $order = $this->orderRepository->findById($id);
            if (in_array($order->payment_method, ['cash', 'cash_on_delivery'], true) || ! $order->shipping_receipt) {
                throw ValidationException::withMessages([
                    'receipt' => 'This order does not have a prepaid payment receipt to review.',
                ]);
            }

            if (! $approved && ! trim((string) $reason)) {
                throw ValidationException::withMessages([
                    'reason' => 'A rejection reason is required.',
                ]);
            }

            $order->update([
                'payment_receipt_status' => $approved ? 'approved' : 'rejected',
                'payment_status' => $approved ? 'paid' : 'failed',
                'receipt_reviewed_by' => $reviewerId,
                'receipt_reviewed_at' => now(),
                'receipt_rejection_reason' => $approved ? null : trim((string) $reason),
            ]);

            $this->auditService->record(
                $approved ? 'order.receipt_approved' : 'order.receipt_rejected',
                $order,
                ['reason' => $approved ? null : trim((string) $reason)],
                $reviewerId,
            );

            $this->createPaymentReceiptNotification($order, $approved);

            return $order->refresh();
        });
    }

    private function normalizeDeliveryMethod(?string $method): string
    {
        $method = $method ?: 'home_delivery';

        return in_array($method, ['clinic_pickup', 'pharmacy_pickup', 'home_delivery', 'qadmous'], true)
            ? $method
            : 'home_delivery';
    }

    private function resolveDeliveryFee(string $deliveryMethod, $deliveryAreaId): array
    {
        if ($deliveryMethod !== 'home_delivery') {
            return [0.0, 0.0];
        }

        if (!$deliveryAreaId || !Schema::hasTable('delivery_areas')) {
            return [0.0, null];
        }

        $area = DeliveryArea::query()
            ->where('is_active', true)
            ->find($deliveryAreaId);

        return $area
            ? [(float) $area->fee, $area->fee_usd !== null ? (float) $area->fee_usd : null]
            : [0.0, null];
    }

    private function resolveDefaultDeliveryUserId(): ?int
    {
        try {
            return User::role('delivery')->oldest()->value('id');
        } catch (Exception) {
            return null;
        }
    }

    private function resolveNextOrderCoupon(int $userId, float $subtotal, ?float $subtotalUsd): array
    {
        if (!Schema::hasTable('coupons') || $subtotal <= 0) {
            return [null, 0.0, $subtotalUsd === null ? null : 0.0];
        }

        $coupon = Coupon::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->oldest()
            ->first();

        if (!$coupon) {
            return [null, 0.0, $subtotalUsd === null ? null : 0.0];
        }

        $discountAmount = $coupon->discount_type === 'percentage'
            ? $subtotal * ((float) $coupon->discount_value / 100)
            : (float) $coupon->discount_value;

        $discountAmountUsd = $subtotalUsd === null
            ? null
            : ($coupon->discount_type === 'percentage'
                ? $subtotalUsd * ((float) $coupon->discount_value / 100)
                : ($coupon->discount_value_usd !== null ? (float) $coupon->discount_value_usd : null));

        return [
            $coupon,
            min($subtotal, round($discountAmount, 2)),
            $discountAmountUsd === null ? null : min($subtotalUsd, round($discountAmountUsd, 2)),
        ];
    }

    private function assertInventoryAvailable(Product $product, int $quantity, array $visitedProductIds = []): void
    {
        if (in_array($product->id, $visitedProductIds, true)) {
            throw ValidationException::withMessages([
                'items' => "Bundle {$product->name_en} contains a circular product reference.",
            ]);
        }

        $visitedProductIds[] = $product->id;
        if ($product->catalog_type === 'bundle' && ! empty($product->bundle_product_ids)) {
            foreach (collect($product->bundle_product_ids)->map(fn ($id) => (int) $id)->filter()->unique() as $componentId) {
                $component = Product::query()->find($componentId);
                if (! $component) {
                    throw ValidationException::withMessages([
                        'items' => "A product in bundle {$product->name_en} is no longer available.",
                    ]);
                }
                $this->assertInventoryAvailable($component, $quantity, $visitedProductIds);
            }
            return;
        }

        if ($product->track_inventory && $product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'items' => "Only {$product->stock_quantity} units of {$product->name_en} are available.",
            ]);
        }
    }

    private function reserveOrderInventory(Order $order): void
    {
        if (! Schema::hasColumn('orders', 'inventory_reserved_at') || $order->inventory_reserved_at) {
            return;
        }

        $movements = [];
        foreach ($order->items as $item) {
            $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);
            $this->reserveInventory($product, (int) $item->quantity, [], $movements);
        }

        foreach ($movements as $movement) {
            InventoryMovement::create($movement + [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'reason' => "Reserved for confirmed order #{$order->id}",
            ]);
        }

        $order->update(['inventory_reserved_at' => now()]);
        $order->refresh();
    }

    private function reserveInventory(Product $product, int $quantity, array $visitedProductIds = [], array &$movements = []): bool
    {
        if (in_array($product->id, $visitedProductIds, true)) {
            throw ValidationException::withMessages([
                'items' => "Bundle {$product->name_en} contains a circular product reference.",
            ]);
        }

        $visitedProductIds[] = $product->id;

        if ($product->catalog_type === 'bundle' && ! empty($product->bundle_product_ids)) {
            $reservedAnyInventory = false;
            $componentIds = collect($product->bundle_product_ids)
                ->map(fn ($productId) => (int) $productId)
                ->filter()
                ->unique()
                ->sort()
                ->values();

            foreach ($componentIds as $componentId) {
                $component = Product::query()->lockForUpdate()->find($componentId);
                if (! $component) {
                    throw ValidationException::withMessages([
                        'items' => "A product in bundle {$product->name_en} is no longer available.",
                    ]);
                }

                $componentReserved = $this->reserveInventory($component, $quantity, $visitedProductIds, $movements);
                $reservedAnyInventory = $reservedAnyInventory || $componentReserved;
            }

            return $reservedAnyInventory;
        }

        if (! $product->track_inventory) {
            return false;
        }

        if ($product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'items' => "Only {$product->stock_quantity} units of {$product->name_en} are available.",
            ]);
        }

        $stockBefore = (int) $product->stock_quantity;
        $product->decrement('stock_quantity', $quantity);
        $product->refresh();
        $movements[] = [
            'product_id' => $product->id,
            'type' => 'reservation',
            'quantity_change' => -$quantity,
            'stock_before' => $stockBefore,
            'stock_after' => (int) $product->stock_quantity,
        ];
        $this->notifyLowStock($product, $stockBefore);

        return true;
    }

    private function releaseInventory(Order $order): void
    {
        if (! Schema::hasColumn('orders', 'inventory_reserved_at')
            || ! $order->inventory_reserved_at
            || $order->inventory_released_at) {
            return;
        }

        $released = Order::query()
            ->whereKey($order->id)
            ->whereNull('inventory_released_at')
            ->update(['inventory_released_at' => now()]);

        if ($released !== 1) {
            return;
        }

        foreach ($order->items as $item) {
            $product = Product::query()->lockForUpdate()->find($item->product_id);
            if ($product) {
                $this->releaseProductInventory($product, (int) $item->quantity, [], $order);
            }
        }
    }

    private function releaseProductInventory(Product $product, int $quantity, array $visitedProductIds = [], ?Order $order = null): void
    {
        if (in_array($product->id, $visitedProductIds, true)) {
            return;
        }

        $visitedProductIds[] = $product->id;

        if ($product->catalog_type === 'bundle' && ! empty($product->bundle_product_ids)) {
            $componentIds = collect($product->bundle_product_ids)
                ->map(fn ($productId) => (int) $productId)
                ->filter()
                ->unique()
                ->sort()
                ->values();

            foreach ($componentIds as $componentId) {
                $component = Product::query()->lockForUpdate()->find($componentId);
                if ($component) {
                    $this->releaseProductInventory($component, $quantity, $visitedProductIds, $order);
                }
            }

            return;
        }

        if ($product->track_inventory) {
            $stockBefore = (int) $product->stock_quantity;
            $product->increment('stock_quantity', $quantity);
            $product->refresh();
            InventoryMovement::create([
                'product_id' => $product->id,
                'order_id' => $order?->id,
                'type' => 'release',
                'quantity_change' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => (int) $product->stock_quantity,
                'reason' => $order ? "Released after order #{$order->id} cancellation" : 'Inventory released',
            ]);
            if ($product->stock_quantity > $product->low_stock_threshold && $product->low_stock_notified_at) {
                $product->update(['low_stock_notified_at' => null]);
            }
        }
    }

    private function resolveActiveOfferDiscount(int $userId, float $subtotal, ?float $subtotalUsd): array
    {
        $offer = $this->offerService->getActiveForUser(User::find($userId));
        if (! $offer || $subtotal <= 0) {
            return [null, 0.0, $subtotalUsd === null ? null : 0.0];
        }

        $discountAmount = $offer->discount_type === 'percentage'
            ? $subtotal * ((float) $offer->discount_value / 100)
            : (float) $offer->discount_value;

        $discountAmountUsd = $subtotalUsd === null
            ? null
            : ($offer->discount_type === 'percentage'
                ? $subtotalUsd * ((float) $offer->discount_value / 100)
                : ($offer->discount_value_usd !== null ? (float) $offer->discount_value_usd : null));

        return [
            $offer,
            min($subtotal, round($discountAmount, 2)),
            $discountAmountUsd === null ? null : min($subtotalUsd, round($discountAmountUsd, 2)),
        ];
    }

    private function notifyLowStock(Product $product, int $stockBefore): void
    {
        if ($product->stock_quantity > $product->low_stock_threshold || $product->low_stock_notified_at) {
            return;
        }

        foreach (User::role('admin')->pluck('id') as $staffId) {
            Notification::create([
                'user_id' => $staffId,
                'title' => 'تنبيه مخزون',
                'body' => "وصل مخزون {$product->name_ar} إلى {$product->stock_quantity} قطعة.",
                'type' => 'low_stock',
                'data' => [
                    'product_id' => $product->id,
                    'stock_before' => $stockBefore,
                    'stock_after' => (int) $product->stock_quantity,
                ],
            ]);
        }

        $product->update(['low_stock_notified_at' => now()]);
    }

    private function createPaymentReceiptNotification(Order $order, bool $approved): void
    {
        Notification::create([
            'user_id' => $order->user_id,
            'title' => $approved ? 'تم اعتماد الدفع' : 'تعذر اعتماد الدفع',
            'body' => $approved
                ? "تم اعتماد إيصال الدفع للطلب #{$order->id}."
                : "تم رفض إيصال الدفع للطلب #{$order->id}: {$order->receipt_rejection_reason}",
            'type' => $approved ? 'payment_approved' : 'payment_rejected',
            'data' => ['order_id' => $order->id, 'receipt_status' => $order->payment_receipt_status],
        ]);
    }

    private function createOrderNotification($order, string $type): void
    {
        $statusText = $this->statusLabel($order->status);

        $content = match ($type) {
            'order_created' => [
                'title' => 'تم استلام طلبك',
                'body' => "وصلنا طلبك رقم #{$order->id}. سنرسل لك إشعاراً عند قبوله أو تجهيزه.",
                'title_en' => 'Order received',
                'body_en' => "We received your order #{$order->id}. We will notify you when it is accepted or ready.",
            ],
            'order_accepted' => [
                'title' => 'تم قبول الطلب',
                'body' => "تم قبول طلبك رقم #{$order->id} وهو قيد التجهيز الآن.",
                'title_en' => 'Order accepted',
                'body_en' => "Your order #{$order->id} has been accepted and is now being prepared.",
            ],
            'order_ready' => [
                'title' => 'طلبك جاهز',
                'body' => "طلبك رقم #{$order->id} جاهز للاستلام أو التوصيل.",
                'title_en' => 'Order is ready',
                'body_en' => "Your order #{$order->id} is ready for pickup or delivery.",
            ],
            'order_delivered' => [
                'title' => 'تم تسليم الطلب',
                'body' => "تم تسليم طلبك رقم #{$order->id}. شكراً لثقتك.",
                'title_en' => 'Order delivered',
                'body_en' => "Your order #{$order->id} has been delivered. Thank you.",
            ],
            default => [
                'title' => 'تحديث على الطلب',
                'body' => "حالة طلبك رقم #{$order->id} أصبحت: {$statusText}.",
                'title_en' => 'Order update',
                'body_en' => "Your order #{$order->id} status is now: {$order->status}.",
            ],
        };

        Notification::create([
            'user_id' => $order->user_id,
            'title' => $content['title'],
            'body' => $content['body'],
            'type' => $type,
            'data' => [
                'order_id' => $order->id,
                'status' => $order->status,
                'translations' => [
                    'ar' => [
                        'title' => $content['title'],
                        'body' => $content['body'],
                    ],
                    'en' => [
                        'title' => $content['title_en'],
                        'body' => $content['body_en'],
                    ],
                ],
            ],
        ]);
    }

    private function notificationTypeForStatus(string $status): string
    {
        return match ($status) {
            'accepted' => 'order_accepted',
            'ready' => 'order_ready',
            'delivered' => 'order_delivered',
            default => 'order_status',
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'بانتظار المراجعة',
            'accepted' => 'مقبول',
            'ready' => 'جاهز',
            'paid' => 'مدفوع',
            'shipped' => 'قيد التوصيل',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغي',
            default => $status,
        };
    }
}
