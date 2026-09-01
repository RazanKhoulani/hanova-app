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
            // Mobile recordings are saved as m4a; keep the common audio formats
            // accepted by Android/iOS and browser uploads.
            'file' => 'required_without:message|nullable|file|mimes:jpeg,png,jpg,mp3,wav,m4a,aac,ogg,webm,pdf,doc,docx|mimetypes:image/jpeg,image/png,audio/mpeg,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,audio/aac,audio/ogg,video/webm,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:10240',
            'type' => 'nullable|in:text,image,audio,file',
        ];
    }
}
