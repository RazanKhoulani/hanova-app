<?php

namespace App\Http\Requests\Auth;

use App\Support\SyrianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:'.SyrianPhoneNumber::VALIDATION_REGEX],
            'password' => 'required|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => SyrianPhoneNumber::normalize($this->input('phone')),
        ]);
    }
}
