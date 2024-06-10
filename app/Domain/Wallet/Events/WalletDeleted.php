<?php

namespace App\Domain\Wallet\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class WalletDeleted extends ShouldBeStored
{
    public function __construct(
        public string $walletId
    )
    {
    }
}
