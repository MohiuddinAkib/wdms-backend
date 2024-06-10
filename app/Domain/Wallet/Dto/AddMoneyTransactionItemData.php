<?php

namespace App\Domain\Wallet\Dto;

use Brick\Math\BigDecimal;
use Spatie\LaravelData\Data;

class AddMoneyTransactionItemData extends Data
{
    public function __construct(
        public string $transactionId,
        public string $denominationId,
        public string $type,
        public float|int $value,
        public int $quantity
    ) {
    }

    public function total()
    {
        return (string) BigDecimal::of($this->value)->multipliedBy($this->quantity);
    }
}
