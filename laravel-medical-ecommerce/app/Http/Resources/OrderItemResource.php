<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->relationLoaded('product') ? $this->product : null;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $product ? ($product->name_ar ?: $product->name_en) : null,
            'product' => new ProductResource($this->whenLoaded('product')),
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
            'price_syp' => (float) $this->price,
            'price_usd' => $this->price_usd !== null ? (float) $this->price_usd : null,
            'total' => (float) ($this->price * $this->quantity),
            'total_usd' => $this->price_usd !== null ? (float) ($this->price_usd * $this->quantity) : null,
        ];
    }
}
