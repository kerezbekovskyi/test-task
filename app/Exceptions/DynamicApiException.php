<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

abstract class DynamicApiException extends RuntimeException
{
    public function __construct(string $message, private readonly int $statusCode)
    {
        parent::__construct($message);
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'timestamp' => now()->toJSON(),
            'status' => $this->statusCode,
            'error' => JsonResponse::$statusTexts[$this->statusCode] ?? 'Error',
            'message' => $this->getMessage(),
            'path' => '/'.ltrim($request->path(), '/'),
        ], $this->statusCode);
    }
}
