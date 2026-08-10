<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_address' => 'nullable|required_if:delivery_method,home_delivery|string|max:1000',
            'payment_method' => 'required|string|in:cash,online,credit_card,cash_on_delivery,apple_pay',
            'delivery_method' => 'required|string|in:clinic_pickup,pharmacy_pickup,home_delivery',
            'pickup_location' => 'nullable|string|in:clinic,pharmacy',
            'delivery_area_id' => [
                'nullable',
                'required_if:delivery_method,home_delivery',
                Rule::exists('delivery_areas', 'id')->where('is_active', true),
            ],
            'items' => 'nullable|array|min:1|max:50',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1|max:99',
        ];
    }
}
