<?php

namespace App\Http\Requests\Auth;

use App\Support\SyrianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:'.SyrianPhoneNumber::VALIDATION_REGEX, 'unique:users,phone'],
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => SyrianPhoneNumber::normalize($this->input('phone')),
        ]);
    }
}
