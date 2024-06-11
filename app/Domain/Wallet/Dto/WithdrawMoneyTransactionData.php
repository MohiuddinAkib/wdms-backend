<?php

namespace App\Domain\Wallet\Dto;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class WithdrawMoneyTransactionData
{
    /** @param array<WithdrawMoneyTransactionItemData> $denominations */
    public function __construct(
        public string $walletUuid,
        public array $denominations
    ) {
    }

    public function total()
    {
        return collect($this->denominations)->reduce(function (string $carry, WithdrawMoneyTransactionItemData $denomination) {
            return (string) BigDecimal::of($carry)->plus($denomination->total())->toScale(2, RoundingMode::DOWN);
        }, '0');
    }
}
