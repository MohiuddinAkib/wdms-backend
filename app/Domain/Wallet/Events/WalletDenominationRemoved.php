<?php

namespace App\Domain\Wallet\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class WalletDenominationRemoved extends ShouldBeStored
{
    public function __construct(
        public string $denominationId
    )
    {
    }
}
