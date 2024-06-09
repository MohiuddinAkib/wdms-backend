<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Dto\CreateWalletData;
use App\Domain\Wallet\Events\WalletCreated;
use App\Domain\Wallet\Exceptions\WalletAlreadyCreatedException;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class WalletAggregate extends AggregateRoot
{
    private bool $created = false;

    public function createWallet(string $userId, string $currency): self
    {
        throw_if($this->created, WalletAlreadyCreatedException::class);
        
        $this->recordThat(new WalletCreated(
            walletId: $this->uuid(),
            userId: $userId,
            currency: $currency
        ));

        return $this;
    }

    protected function applyCreateWallet(WalletCreated $event): void
    {
        $this->created = true;
    }
}
