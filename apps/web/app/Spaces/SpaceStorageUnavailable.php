<?php

namespace App\Spaces;

use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class SpaceStorageUnavailable extends RuntimeException
{
    public function __construct(string $message = 'Spaces are unavailable right now.', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function render(Request $request): Response
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'error' => 'storage_unavailable',
                'message' => $this->getMessage(),
            ], 503);
        }

        return response()->view('errors.storage', status: 503);
    }
}
