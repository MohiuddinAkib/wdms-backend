<?php

namespace App\Domain\Wallet\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class WalletDenominationAdded extends ShouldBeStored
{
    public function __construct(
        public string $walletId,
        public string $denominationId,
        public string $name,
        public string $type
    )
    {
    }
}
