<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class AvailableAppointmentSlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => 'nullable|exists:users,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'type' => 'nullable|in:clinic,online',
            'appointment_type' => 'nullable|in:consultation,session,treatment',
        ];
    }
}
