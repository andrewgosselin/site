<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a standardized success response.
     */
    protected function success(mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => array_merge([
                'success' => true,
                'timestamp' => now()->toIso8601String(),
            ], $meta),
        ], $status);
    }

    /**
     * Return a standardized error response.
     */
    protected function error(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'data' => [
                'message' => $message,
            ],
            'meta' => [
                'success' => false,
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        if ($errors) {
            $response['data']['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
