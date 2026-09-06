<?php

namespace App\Http\Resources;

use App\Services\ProtectedFileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->query('lang', $request->header('Accept-Language', 'ar'));
        $lang = str_starts_with((string) $lang, 'en') ? 'en' : 'ar';

        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => $this->statusLabel($this->status, $lang),
            'total_amount' => (float) $this->total_amount,
            'subtotal_amount' => (float) ($this->subtotal_amount ?? 0),
            'subtotal_usd' => $this->subtotal_usd !== null ? (float) $this->subtotal_usd : null,
            'total_amount_usd' => $this->total_amount_usd !== null ? (float) $this->total_amount_usd : null,
            'currency_code' => config('app.currency_code', 'SYP'),
            'currency_symbol' => config('app.currency_symbol', 'ل.س'),
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'payment_receipt_status' => $this->payment_receipt_status,
            'payment_receipt_url' => $this->shipping_receipt
                ? ProtectedFileUrl::make('order-receipt', $this->id, 'receipt', $request->user()?->id)
                : null,
            'receipt_rejection_reason' => $this->receipt_rejection_reason,
            'delivery_method' => $this->delivery_method,
            'qadmous_governorate' => $this->qadmous_governorate,
            'qadmous_branch' => $this->qadmous_branch,
            'qadmous_location_id' => $this->qadmous_location_id,
            'recipient_name' => $this->recipient_name,
            'recipient_phone' => $this->recipient_phone,
            'tracking_number' => $this->tracking_number,
            'pickup_location' => $this->pickup_location,
            'delivery_area_id' => $this->delivery_area_id,
            'delivery_fee' => (float) ($this->delivery_fee ?? 0),
            'delivery_fee_usd' => $this->delivery_fee_usd !== null ? (float) $this->delivery_fee_usd : null,
            'discount_amount' => (float) ($this->discount_amount ?? 0),
            'discount_amount_usd' => $this->discount_amount_usd !== null ? (float) $this->discount_amount_usd : null,
            'coupon' => $this->whenLoaded('coupon', function () {
                return $this->coupon ? [
                    'id' => $this->coupon->id,
                    'code' => $this->coupon->code,
                    'discount_type' => $this->coupon->discount_type,
                    'discount_value' => (float) $this->coupon->discount_value,
                    'discount_value_usd' => $this->coupon->discount_value_usd !== null ? (float) $this->coupon->discount_value_usd : null,
                    'source' => $this->coupon->source,
                ] : null;
            }),
            'applied_offer' => $this->whenLoaded('appliedOffer', function () {
                return $this->appliedOffer ? [
                    'id' => $this->appliedOffer->id,
                    'title' => $this->appliedOffer->title_ar,
                    'discount_type' => $this->appliedOffer->discount_type,
                    'discount_value' => (float) $this->appliedOffer->discount_value,
                    'discount_value_usd' => $this->appliedOffer->discount_value_usd !== null ? (float) $this->appliedOffer->discount_value_usd : null,
                ] : null;
            }),
            'delivery_area' => $this->whenLoaded('deliveryArea', function () use ($lang) {
                if (!$this->deliveryArea) {
                    return null;
                }

                return [
                    'id' => $this->deliveryArea->id,
                    'name' => $lang === 'en' ? $this->deliveryArea->name_en : $this->deliveryArea->name_ar,
                    'fee' => (float) $this->deliveryArea->fee,
                ];
            }),
            'delivery_user_id' => $this->delivery_user_id,
            'delivery_user' => $this->whenLoaded('deliveryUser', function () {
                return $this->deliveryUser ? [
                    'id' => $this->deliveryUser->id,
                    'name' => $this->deliveryUser->name,
                    'phone' => $this->deliveryUser->phone,
                ] : null;
            }),
            'shipping_address' => $this->shipping_address,
            'shipping_latitude' => $this->shipping_latitude !== null ? (float) $this->shipping_latitude : null,
            'shipping_longitude' => $this->shipping_longitude !== null ? (float) $this->shipping_longitude : null,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }

    private function statusLabel(?string $status, string $lang): string
    {
        $labels = [
            'pending' => ['ar' => 'بانتظار المراجعة', 'en' => 'Pending'],
            'accepted' => ['ar' => 'مقبول', 'en' => 'Accepted'],
            'ready' => ['ar' => 'جاهز', 'en' => 'Ready'],
            'paid' => ['ar' => 'مدفوع', 'en' => 'Paid'],
            'shipped' => ['ar' => 'قيد التوصيل', 'en' => 'Shipped'],
            'delivered' => ['ar' => 'تم التسليم', 'en' => 'Delivered'],
            'cancelled' => ['ar' => 'ملغي', 'en' => 'Cancelled'],
        ];

        return $labels[$status][$lang] ?? (string) $status;
    }
}
