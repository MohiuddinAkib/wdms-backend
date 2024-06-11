<?php

namespace App\Domain\Wallet\Events;

use App\Domain\Wallet\Dto\WithdrawMoneyTransactionData;
use Carbon\Carbon;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class MoneyWithdrawn extends ShouldBeStored
{
    public function __construct(
        public string $walletId,
        public WithdrawMoneyTransactionData $transactionData,
        public Carbon $happenedAt
    )
    {
    }
}
