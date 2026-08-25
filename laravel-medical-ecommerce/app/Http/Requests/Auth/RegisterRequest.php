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
            'email' => 'nullable|email|max:255|unique:users,email',
            'phone' => ['required', 'string', 'regex:'.SyrianPhoneNumber::VALIDATION_REGEX],
            'phone_confirmation' => ['required', 'string', 'same:phone'],
            'password' => 'required|string|min:6',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => SyrianPhoneNumber::normalize($this->input('phone')),
            'phone_confirmation' => SyrianPhoneNumber::normalize($this->input('phone_confirmation')),
        ]);
    }
}
