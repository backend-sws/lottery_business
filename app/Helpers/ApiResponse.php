<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = [], $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error($message = 'Error', $code = 500, $data = null)
    {
        $payload = [
            'success' => false,
            'message' => $message
        ];
        if ($data !== null) {
            $payload['data'] = $data;
        }
        return response()->json($payload, $code);
    }
}