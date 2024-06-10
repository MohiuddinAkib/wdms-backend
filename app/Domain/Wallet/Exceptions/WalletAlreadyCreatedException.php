<?php

namespace App\Domain\Wallet\Exceptions;

use DomainException;
use Illuminate\Http\JsonResponse;

class WalletAlreadyCreatedException extends DomainException
{
    public function __construct(string $currency)
    {
        parent::__construct("Wallet already exists with the currency: " . $currency);   
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage()
        ]);
    }
}