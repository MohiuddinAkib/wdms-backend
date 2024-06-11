<?php

namespace App\Domain\Wallet\Projectors;

use App\Domain\Wallet\Events\MoneyAdded;
use App\Domain\Wallet\Events\MoneyWithdrawn;
use App\Domain\Wallet\Events\WalletCreated;
use App\Domain\Wallet\Events\WalletDeleted;
use App\Domain\Wallet\Events\WalletDenominationAdded;
use App\Domain\Wallet\Events\WalletDenominationRemoved;
use App\Domain\Wallet\Projections\Denomination;
use App\Domain\Wallet\Projections\Transaction;
use App\Domain\Wallet\Projections\Wallet;
use App\Models\User;
use DB;
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
            'value' => $event->value,
            'wallet_id' => $event->walletId,
        ]);
    }

    public function onWalletDenominationRemoved(WalletDenominationRemoved $event): void
    {
        Denomination::find($event->denominationId)?->writeable()->delete();
    }

    public function onMoneyAdded(MoneyAdded $event): void
    {
        // REFACTOR INTO SAGA
        DB::transaction(function () use ($event) {
            $denominations = $event->transactionData->denominations;

            Wallet::find($event->walletId)
                ->writeable()
                ->deposit($event->transactionData->total());

            foreach ($denominations as $denomination) {
                Transaction::new()->writeable()->create([
                    'uuid' => $denomination->transactionId,
                    'wallet_id' => $event->walletId,
                    'group_id' => $denomination->transactionGroupId,
                    'denomination_id' => $denomination->denominationId,
                    'type' => 'add',
                    'quantity' => $denomination->quantity,
                    'happened_at' => $event->happenedAt,
                ]);

                Denomination::where('wallet_id', $event->walletId)
                    ->find($denomination->denominationId)
                    ->writeable()
                    ->increment('quantity', $denomination->quantity);
            }
        });
    }

    public function onMoneyWithdrawn(MoneyWithdrawn $event): void
    {
        // REFACTOR INTO SAGA
        DB::transaction(function () use ($event) {
            $denominations = $event->transactionData->denominations;

            Wallet::find($event->walletId)
                ->writeable()
                ->withdraw($event->transactionData->total());

            foreach ($denominations as $denomination) {
                Transaction::new()->writeable()->create([
                    'uuid' => $denomination->transactionId,
                    'wallet_id' => $event->walletId,
                    'group_id' => $denomination->transactionGroupId,
                    'denomination_id' => $denomination->denominationId,
                    'type' => 'withdraw',
                    'quantity' => $denomination->quantity,
                    'happened_at' => $event->happenedAt,
                ]);

                Denomination::where('wallet_id', $event->walletId)
                    ->find($denomination->denominationId)
                    ->writeable()
                    ->decrement('quantity', $denomination->quantity);
            }
        });
    }
}
