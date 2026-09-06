<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\ProtectedFileUrl;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'record_code' => $this->record_code,
            'name' => $this->name,
            'age' => $this->age,
            'phone' => $this->phone,
            'address' => $this->address,
            'notes' => $this->notes,
            'images' => [
                'before' => $this->image_before ? ProtectedFileUrl::make('patient', $this->id, 'before', $request->user()?->id) : null,
                'after' => $this->image_after ? ProtectedFileUrl::make('patient', $this->id, 'after', $request->user()?->id) : null,
            ],
            'progress_photos' => $this->whenLoaded('progressPhotos', function () use ($request) {
                return $this->progressPhotos->map(fn ($photo) => [
                    'id' => $photo->id,
                    'before_image' => ProtectedFileUrl::make('progress-photo', $photo->id, 'before', $request->user()?->id),
                    'after_image' => ProtectedFileUrl::make('progress-photo', $photo->id, 'after', $request->user()?->id),
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
            'medical_file' => $this->medical_file ? ProtectedFileUrl::make('patient', $this->id, 'medical', $request->user()?->id) : null,
            'created_at' => $this->created_at,
        ];
    }
}
