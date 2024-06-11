<?php

namespace App\Domain\Wallet\Resource;

use App\Domain\Wallet\Resources\WalletResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\LaravelData\Resource;

class WithdrawMoneyTransactionResponseResource extends Resource
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
