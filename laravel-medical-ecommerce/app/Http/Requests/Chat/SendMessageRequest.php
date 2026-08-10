<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required_without:file|nullable|string',
            'file' => 'required_without:message|nullable|file|mimes:jpeg,png,jpg,mp3,wav,pdf,doc,docx|max:10240',
            'type' => 'nullable|in:text,image,audio,file',
        ];
    }
}
