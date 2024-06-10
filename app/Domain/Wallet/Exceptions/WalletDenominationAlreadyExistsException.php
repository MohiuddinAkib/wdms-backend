<?php

namespace App\Domain\Wallet\Exceptions;

use DomainException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WalletDenominationAlreadyExistsException extends DomainException
{
    public function __construct(string $name, string $type)
    {
        parent::__construct("The denomination name: {$name}, type: {$type} has already been taken for this wallet.");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }
}
