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
            'identifier' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string'],
            'password' => 'required|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $identifier = trim((string) ($this->input('identifier') ?: $this->input('phone')));
        $identifier = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? strtolower($identifier)
            : SyrianPhoneNumber::normalize($identifier);

        $this->merge(['identifier' => $identifier]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $identifier = (string) $this->input('identifier');
            if ($identifier === '') {
                $validator->errors()->add('identifier', 'Phone number or email is required.');
            }
            if (!filter_var($identifier, FILTER_VALIDATE_EMAIL) && !preg_match('/'.trim(SyrianPhoneNumber::VALIDATION_REGEX, '/').'/u', $identifier)) {
                $validator->errors()->add('identifier', 'Enter a valid Syrian phone number or email address.');
            }
        });
    }
}
