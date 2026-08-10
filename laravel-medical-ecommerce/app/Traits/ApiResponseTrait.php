<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function successResponse($data = null, $message = 'Success', $code = 200)
    {
        $response = [
            'status' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    public function errorResponse($message = 'Error', $code = 400, $errors = [])
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
