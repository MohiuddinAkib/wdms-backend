<?php

namespace App\Domain\Wallet\Resources;

use Spatie\LaravelData\Resource;

class WalletDetailsResponseResource extends Resource
{
    public function __construct(
        public bool $success,
        public WalletResource $data,
    ) {
    }
}
