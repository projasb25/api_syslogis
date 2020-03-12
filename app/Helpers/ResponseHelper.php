<?php

namespace App\Helpers;

class ResponseHelper # implements ResponseInterface
{
    public static function error($data, $http_code)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'mensaje' => $data[0],
                'code' => $data[1]
            ]
        ], $http_code);
    }

    public static function success($data)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'mensaje' => $data
            ]
        ]);
    }
}
