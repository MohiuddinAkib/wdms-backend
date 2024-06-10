<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Events\WalletCreated;
use App\Domain\Wallet\Events\WalletDeleted;
use App\Domain\Wallet\Exceptions\WalletAlreadyCreatedException;
use App\Domain\Wallet\Exceptions\WalletBalanceNotEmptyException;
use Brick\Math\BigDecimal;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class WalletAggregate extends AggregateRoot
{
    private bool $created = false;

    private string $balance = '0';

    public function createWallet(string $userId, string $currency): self
    {
        throw_if($this->created, WalletAlreadyCreatedException::class, $currency);

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

    public function deleteWallet(): self
    {
        if (BigDecimal::of($this->balance)->compareTo(0) > 0) {
            throw new WalletBalanceNotEmptyException();
        }

        $this->recordThat(new WalletDeleted(
            walletId: $this->uuid()
        ));

        return $this;
    }
}
