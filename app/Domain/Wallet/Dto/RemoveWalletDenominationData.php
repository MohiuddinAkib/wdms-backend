<?php

namespace App\Domain\Wallet\Dto;

use Spatie\LaravelData\Data;

class RemoveWalletDenominationData
{
    public function __construct(
        public readonly string $denominationId,
        public readonly string $type,
        public readonly string $name,
        public readonly int $quantity,
    ) {
    }
}
