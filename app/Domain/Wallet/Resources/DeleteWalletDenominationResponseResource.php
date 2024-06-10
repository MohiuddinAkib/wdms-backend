<?php

namespace App\Domain\Wallet\Resources;

use Spatie\LaravelData\Resource;

class DeleteWalletDenominationResponseResource extends Resource
{
    public function __construct(
      public bool $success,
      public string $message
    ) {}
}
