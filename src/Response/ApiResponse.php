<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    public function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function error(
        string $message = 'An error occurred',
        int $status = Response::HTTP_BAD_REQUEST,
    ): JsonResponse {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
