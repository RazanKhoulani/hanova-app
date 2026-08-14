<?php

namespace App\Http\Requests\Auth;

use App\Support\SyrianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class VerifyRegistrationOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:'.SyrianPhoneNumber::VALIDATION_REGEX, 'exists:users,phone'],
            'otp' => 'required|string|size:4',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => SyrianPhoneNumber::normalize($this->input('phone')),
        ]);
    }
}
