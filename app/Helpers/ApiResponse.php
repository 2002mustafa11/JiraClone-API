<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = [], $message = 'نجاح', $status = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error($message = 'خطأ في الطلب', $status = 400, $errors = [])
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
}
