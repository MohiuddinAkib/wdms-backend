<?php

namespace App\Domain\Wallet\Resources;

use Spatie\LaravelData\Resource;

class AddWalletDenominationResponseResource extends Resource
{
    public function __construct(
      public bool $success,
      public WalletResource $data
    ) {}
}
