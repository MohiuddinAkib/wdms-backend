<?php

namespace App\Domain\Wallet\Dto;

use Spatie\LaravelData\Data;

class AddWalletDenominationData extends Data
{
    public function __construct(
        public string $denominationId,
        public string $name,
        public float|int $value,
        public string $type,
    ) {
    }
}
