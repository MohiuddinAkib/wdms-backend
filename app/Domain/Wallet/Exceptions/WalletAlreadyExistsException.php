<?php

namespace App\Domain\Wallet\Exceptions;

use DomainException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WalletAlreadyExistsException extends DomainException
{
    public function __construct(string $currency)
    {
        parent::__construct('Wallet already exists with the currency: '.$currency);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }
}
