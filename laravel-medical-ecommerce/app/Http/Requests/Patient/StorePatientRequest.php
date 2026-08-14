<?php

namespace App\Http\Requests\Patient;

use App\Support\SyrianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            'phone' => ['required', 'string', 'regex:'.SyrianPhoneNumber::VALIDATION_REGEX],
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'medical_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx|max:5120',
            'image_before' => 'nullable|image|max:5120',
            'image_after' => 'nullable|image|max:5120',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => SyrianPhoneNumber::normalize($this->input('phone')),
        ]);
    }
}
