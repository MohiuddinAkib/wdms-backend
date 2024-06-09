<?php

namespace App\Domain\Wallet\Projectors;

use App\Domain\Wallet\Events\WalletCreated;
use App\Domain\Wallet\Projections\Wallet;
use App\Models\User;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class WalletsProjector extends Projector
{
    public function onWalletCreated(WalletCreated $event): void
    {
        Wallet::new()->writeable()->create([
            'uuid' => $event->walletId,
            'currency' => $event->currency,
            User::getModel()->getForeignKey() => $event->userId,
        ]);
    }
}
