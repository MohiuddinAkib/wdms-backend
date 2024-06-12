<?php

namespace App\Domain\Wallet\Exceptions;

use DomainException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WalletAlreadyRemovedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Wallet already removed');
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }
}