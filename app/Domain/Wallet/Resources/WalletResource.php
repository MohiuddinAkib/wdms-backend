<?php

namespace App\Domain\Wallet\Resources;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Resource;

#[MapOutputName(SnakeCaseMapper::class)]
class WalletResource extends Resource
{
    public function __construct(
        public string $uuid,
        public string $currency,
        public string $balance,
    ) {
    }
}
