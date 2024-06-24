<?php

namespace App\Domain\Wallet\Events;

use App\Domain\Wallet\Dto\AddMoneyTransactionData;
use Carbon\Carbon;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class MoneyAdded extends ShouldBeStored
{
    public function __construct(
        public string $walletId,
        public string $walletCurrency,
        public AddMoneyTransactionData $transactionData,
        public Carbon $happenedAt
    ) {
    }
}
