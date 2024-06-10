<?php

namespace App\Domain\Wallet\Projectors;

use App\Domain\Currency\Projections\Denomination;
use App\Domain\Wallet\Events\WalletCreated;
use App\Domain\Wallet\Events\WalletDeleted;
use App\Domain\Wallet\Events\WalletDenominationAdded;
use App\Domain\Wallet\Events\WalletDenominationRemoved;
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

    public function onWalletDeleted(WalletDeleted $event): void
    {
        Wallet::find($event->walletId)?->writeable()->delete();
    }

    public function onWalletDenominationAdded(WalletDenominationAdded $event): void
    {
        Denomination::new()->writeable()->create([
            'uuid' => $event->denominationId,
            'name' => $event->name,
            'type' => $event->type,
            'wallet_id' => $event->walletId
        ]);
    }

    public function onWalletDenominationRemoved(WalletDenominationRemoved $event): void
    {
        Denomination::find($event->denominationId)?->writeable()->delete();
    }
}
