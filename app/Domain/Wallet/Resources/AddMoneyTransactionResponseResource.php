<?php

namespace App\Domain\Wallet\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\LaravelData\Resource;

class AddMoneyTransactionResponseResource extends Resource
{
    public function __construct(
      public bool $success,
      public string $message,
      public WalletResource $data
      ) {}

      protected function calculateResponseStatus(Request $request): int
      {
        return Response::HTTP_OK;
      }
}
