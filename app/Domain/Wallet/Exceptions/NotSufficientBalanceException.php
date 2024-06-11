<?php

namespace App\Domain\Wallet\Exceptions;

use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotSufficientBalanceException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Not sufficient balance.');
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }
}
