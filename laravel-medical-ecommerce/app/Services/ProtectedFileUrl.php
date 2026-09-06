<?php

namespace App\Services;

use Illuminate\Support\Facades\URL;

class ProtectedFileUrl
{
    public static function make(string $kind, int $id, string $field, ?int $viewerId = null): string
    {
        $parameters = [
            'kind' => $kind,
            'id' => $id,
            'field' => $field,
        ];

        if ($viewerId) {
            $parameters['viewer'] = $viewerId;
        }

        return URL::temporarySignedRoute(
            'secure-files.show',
            now()->addMinutes(30),
            $parameters,
        );
    }
}
