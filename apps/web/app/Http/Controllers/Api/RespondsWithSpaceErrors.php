<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

trait RespondsWithSpaceErrors
{
    protected function spaceError(string $error, string $message, int $status): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'error' => $error,
            'message' => $message,
        ], $status);
    }
}
