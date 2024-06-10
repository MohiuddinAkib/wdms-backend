<?php

namespace App\Domain\Wallet\Resources;

use Spatie\LaravelData\Resource;

class CreateWalletResponseResource extends Resource
{
    public function __construct(
        public bool $success,
        public string $message,
        public ?WalletResource $data
    ) {
    }
}
