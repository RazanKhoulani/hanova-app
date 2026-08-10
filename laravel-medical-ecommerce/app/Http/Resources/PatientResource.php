<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'age' => $this->age,
            'phone' => $this->phone,
            'address' => $this->address,
            'notes' => $this->notes,
            'images' => [
                'before' => $this->image_before ? Storage::url($this->image_before) : null,
                'after' => $this->image_after ? Storage::url($this->image_after) : null,
            ],
            'progress_photos' => $this->whenLoaded('progressPhotos', function () {
                return $this->progressPhotos->map(fn ($photo) => [
                    'id' => $photo->id,
                    'before_image' => Storage::url($photo->before_image),
                    'after_image' => Storage::url($photo->after_image),
                    'status' => $photo->status,
                    'consent_for_discount' => $photo->consent_for_discount,
                    'discount_percent' => (float) $photo->discount_percent,
                    'coupon_code' => $photo->coupon?->code,
                    'created_at' => $photo->created_at,
                ])->values();
            }),
            'medical_facts' => $this->whenLoaded('medicalFacts', function () {
                return $this->medicalFacts->map(fn ($fact) => [
                    'id' => $fact->id,
                    'key' => $fact->key,
                    'value' => $fact->value,
                    'confidence' => (float) $fact->confidence,
                    'status' => $fact->status,
                    'source_message_id' => $fact->source_message_id,
                    'created_at' => $fact->created_at,
                ])->values();
            }),
            'medical_file' => $this->medical_file ? Storage::url($this->medical_file) : null,
            'created_at' => $this->created_at,
        ];
    }
}
