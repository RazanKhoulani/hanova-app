<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:chat,bot,pre_booked',
            'doctor_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ];
    }
}
