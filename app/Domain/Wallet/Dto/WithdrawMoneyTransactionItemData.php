<?php

namespace App\Domain\Wallet\Dto;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class WithdrawMoneyTransactionItemData
{
    public function __construct(
        public string $transactionId,
        public string $transactionGroupId,
        public string $denominationId,
        public string $type,
        public float|int $value,
        public int $quantity
    ) {
    }

    public function total()
    {
        return (string) BigDecimal::of($this->value)->multipliedBy($this->quantity)->toScale(2, RoundingMode::DOWN);
    }
}
