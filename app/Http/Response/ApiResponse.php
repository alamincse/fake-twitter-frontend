<?php

namespace App\Http\Response;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


class ApiResponse
{
    public function validationError($messages = [], $data = [], $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY): JsonResponse
    {
        return response()->json([
            'status'     => 'FAILED',
            'statusCode' => '400300',
            'data'       => $data,
            'message'    => $messages,
        ], $responseCode);
    }

    public function success($data = [], $message = null, $responseCode = Response::HTTP_OK): JsonResponse
    {

        return response()->json([
            'status'     => 'SUCCESS',
            'statusCode' => '400200',
            'data'       => $data,
            'message'    => $message,
        ], $responseCode);
    }

    public function error($message = null, $data = [], $responseCode = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'status'     => 'FAILED',
            'statusCode' => '400500',
            'data'       => $data,
            'message'    => $message,
        ], $responseCode);
    }
}
