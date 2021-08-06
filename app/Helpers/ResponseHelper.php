<?php

namespace App\Helpers;


class ResponseHelper {
    public static function success($data, $message = "Success"): array
    {
        return [ 'success' => true, 'message' => $message, 'data' => $data ];
    }

    public static function error($data, $message = "Error"): array
    {
        return [ 'success' => false, 'message' => $message, 'data' => $data ];
    }
}
