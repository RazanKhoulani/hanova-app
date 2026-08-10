<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = $request->query('lang', $request->header('Accept-Language', 'ar'));
        $lang = str_starts_with((string) $lang, 'en') ? 'en' : 'ar';

        $appointmentDate = null;
        if ($this->date && $this->time) {
            $appointmentDate = "{$this->date} {$this->time}";
        }

        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'patient_name' => optional($this->patient)->name,
            'appointment_date' => $appointmentDate,
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'doctor' => new UserResource($this->whenLoaded('doctor')),
            'date' => $this->date,
            'time' => $this->time,
            'type' => $this->type,
            'appointment_type' => $this->appointment_type,
            'duration_minutes' => $this->duration_minutes,
            'status' => $this->status,
            'status_label' => $this->statusLabel($this->status, $lang),
            'created_at' => $this->created_at,
        ];
    }

    private function statusLabel(?string $status, string $lang): string
    {
        $labels = [
            'pending' => ['ar' => 'بانتظار المراجعة', 'en' => 'Pending'],
            'confirmed' => ['ar' => 'مؤكد', 'en' => 'Confirmed'],
            'completed' => ['ar' => 'مكتمل', 'en' => 'Completed'],
            'cancelled' => ['ar' => 'ملغي', 'en' => 'Cancelled'],
        ];

        return $labels[$status][$lang] ?? (string) $status;
    }
}
