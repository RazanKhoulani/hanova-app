<?php

namespace App\Services;

use App\Models\DeliveryArea;
use App\Models\Coupon;
use App\Models\Notification;
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

    public function __construct(OrderRepository $orderRepository, CartRepository $cartRepository)
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
            $orderItems = [];
            $usesClientItems = !empty($data['items']) && is_array($data['items']);

            if ($usesClientItems) {
                foreach ($data['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $quantity = (int) $item['quantity'];
                    $price = (float) $product->price;

                    $subtotal += $price * $quantity;
                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
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
                    $price = (float) $item->product->price;
                    $quantity = (int) $item->quantity;

                    $subtotal += $price * $quantity;
                    $orderItems[] = [
                        'product_id' => $item->product_id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ];
                }
            }

            $deliveryMethod = $this->normalizeDeliveryMethod($data['delivery_method'] ?? null);
            $deliveryFee = $this->resolveDeliveryFee($deliveryMethod, $data['delivery_area_id'] ?? null);
            $deliveryUserId = $deliveryMethod === 'home_delivery'
                ? $this->resolveDefaultDeliveryUserId()
                : null;
            [$coupon, $discountAmount] = $this->resolveNextOrderCoupon($userId, $subtotal);

            $paymentMethod = $data['payment_method'] ?? 'cash';
            $orderData = [
                'user_id' => $userId,
                'status' => 'pending',
                'shipping_address' => $data['shipping_address'] ?? null,
                'payment_method' => $paymentMethod,
                'total_amount' => max(0, $subtotal - $discountAmount) + $deliveryFee,
            ];

            if (Schema::hasColumn('orders', 'coupon_id')) {
                $orderData['coupon_id'] = $coupon?->id;
                $orderData['discount_amount'] = $discountAmount;
            }

            if (Schema::hasColumn('orders', 'payment_status')) {
                $orderData['payment_status'] = in_array($paymentMethod, ['cash', 'cash_on_delivery'], true)
                    ? 'unpaid'
                    : 'pending';
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
                $orderData['qadmous_governorate'] = $data['qadmous_governorate'];
                $orderData['qadmous_branch'] = $data['qadmous_branch'];
                $orderData['recipient_name'] = $data['recipient_name'];
                $orderData['recipient_phone'] = $data['recipient_phone'];
                $orderData['shipping_address'] = $data['qadmous_governorate'].' - '.$data['qadmous_branch'];
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
                    'body' => "تم استلام طلب جديد رقم #{$order->id} بقيمة {$order->total_amount}.",
                    'type' => 'new_order',
                    'data' => ['order_id' => $order->id],
                ]);
            }

            // Checkout always represents one unified order, so clear the
            // authenticated server cart even when the app sent its item list.
            $cart = $this->cartRepository->getCartForUser($userId);
            $this->cartRepository->clearCart($cart);

            DB::commit();

            return $order->load('items');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateOrderStatus($id, $status)
    {
        $order = $this->orderRepository->findById($id);

        if ($status === 'delivered' && !in_array($order->status, ['accepted', 'ready', 'shipped'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Only an accepted, ready, or shipped order can be marked as delivered.',
            ]);
        }

        $updateData = ['status' => $status];

        if ($status === 'delivered') {
            $updateData['payment_method'] = 'cash';

            if (Schema::hasColumn('orders', 'payment_status')) {
                $updateData['payment_status'] = 'paid';
            }
        }

        if ($status === 'paid' && Schema::hasColumn('orders', 'payment_status')) {
            $updateData['payment_status'] = 'paid';
        }

        $updatedOrder = $this->orderRepository->update($order, $updateData);
        $this->createOrderNotification($updatedOrder, $this->notificationTypeForStatus($status));

        return $updatedOrder;
    }

    public function confirmOrder($id)
    {
        $order = $this->orderRepository->findById($id);

        if (in_array($order->status, ['cancelled', 'delivered'], true)) {
            throw ValidationException::withMessages([
                'status' => 'This order can no longer be confirmed.',
            ]);
        }

        if ($order->status === 'accepted' && $order->is_confirmed) {
            return true;
        }

        $updated = $order->update(['is_confirmed' => true, 'status' => 'accepted']);
        $order->refresh();
        $this->createOrderNotification($order, 'order_accepted');

        return $updated;
    }

    public function updateShippingReceipt($id, $path)
    {
        $order = $this->orderRepository->findById($id);

        return $order->update(['shipping_receipt' => $path]);
    }

    private function normalizeDeliveryMethod(?string $method): string
    {
        $method = $method ?: 'home_delivery';

        return in_array($method, ['clinic_pickup', 'pharmacy_pickup', 'home_delivery', 'qadmous'], true)
            ? $method
            : 'home_delivery';
    }

    private function resolveDeliveryFee(string $deliveryMethod, $deliveryAreaId): float
    {
        if ($deliveryMethod !== 'home_delivery') {
            return 0.0;
        }

        if (!$deliveryAreaId || !Schema::hasTable('delivery_areas')) {
            return 0.0;
        }

        $area = DeliveryArea::query()
            ->where('is_active', true)
            ->find($deliveryAreaId);

        return $area ? (float) $area->fee : 0.0;
    }

    private function resolveDefaultDeliveryUserId(): ?int
    {
        try {
            return User::role('delivery')->oldest()->value('id');
        } catch (Exception) {
            return null;
        }
    }

    private function resolveNextOrderCoupon(int $userId, float $subtotal): array
    {
        if (!Schema::hasTable('coupons') || $subtotal <= 0) {
            return [null, 0.0];
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
            return [null, 0.0];
        }

        $discountAmount = $coupon->discount_type === 'percentage'
            ? $subtotal * ((float) $coupon->discount_value / 100)
            : (float) $coupon->discount_value;

        return [$coupon, min($subtotal, round($discountAmount, 2))];
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
