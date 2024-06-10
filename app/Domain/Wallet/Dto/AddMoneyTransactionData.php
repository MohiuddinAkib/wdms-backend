<?php

namespace App\Domain\Wallet\Dto;

use Brick\Math\BigDecimal;
use Spatie\LaravelData\Data;

class AddMoneyTransactionData extends Data
{
    /** @param array<AddMoneyTransactionItemData> $denominations */
    public function __construct(
        public string $walletUuid,
        public array $denominations
    ) {
    }

    public function total()
    {
        return collect($this->denominations)->reduce(function (string $carry, AddMoneyTransactionItemData $denomination) {
            return (string) BigDecimal::of($carry)->plus($denomination->total());
        }, '0');
    }
}
