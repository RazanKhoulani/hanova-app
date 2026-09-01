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
            // Android may report m4a as audio/mp4 or application/octet-stream;
            // validate the user-facing extension instead of rejecting a valid recording.
            // Allow longer voice notes (up to 25 MB); the mobile recorder can
            // produce large AAC/M4A files on older devices.
            'file' => 'required_without:message|nullable|file|extensions:jpeg,png,jpg,mp3,wav,m4a,aac,ogg,webm,pdf,doc,docx|max:25600',
            'type' => 'nullable|in:text,image,audio,file',
        ];
    }
}
