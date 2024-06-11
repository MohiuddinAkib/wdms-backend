<?php

namespace App\Domain\Wallet\Dto;


class AddWalletDenominationData
{
    public function __construct(
        public string $denominationId,
        public string $name,
        public float|int $value,
        public string $type,
    ) {
    }
}
