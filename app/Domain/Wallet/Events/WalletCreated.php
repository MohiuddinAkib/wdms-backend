<?php

namespace App\Domain\Wallet\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class WalletCreated extends ShouldBeStored
{
    public function __construct(
        public string $walletId,
        public string $userId,
        public string $currency
    )
    {
    }
}
