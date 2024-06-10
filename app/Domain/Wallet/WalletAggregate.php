<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Dto\AddWalletDenominationData;
use App\Domain\Wallet\Dto\RemoveWalletDenominationData;
use App\Domain\Wallet\Events\WalletCreated;
use App\Domain\Wallet\Events\WalletDeleted;
use App\Domain\Wallet\Events\WalletDenominationAdded;
use App\Domain\Wallet\Events\WalletDenominationRemoved;
use App\Domain\Wallet\Exceptions\UnknownDenominationException;
use App\Domain\Wallet\Exceptions\WalletAlreadyExistsException;
use App\Domain\Wallet\Exceptions\WalletBalanceNotEmptyException;
use App\Domain\Wallet\Exceptions\WalletDenominationAlreadyExistsException;
use App\Domain\Wallet\Exceptions\WalletDenominationBalanceExistsException;
use Arr;
use Brick\Math\BigDecimal;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class WalletAggregate extends AggregateRoot
{
    private bool $created = false;

    private string $balance = '0';

    /**
     * @var array<int, string>
     */
    private array $coins = [];

    /**
     * @var array<int, string>
     */
    private array $bills = [];

    /**
     * @var array<int, string>
     */
    private array $denominationIds = [];

    public function createWallet(string $userId, string $currency): self
    {
        throw_if($this->created, WalletAlreadyExistsException::class, $currency);

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

    public function addDenomination(string $denominationId, AddWalletDenominationData $denominationData): self
    {
        // CHECKING IF THE DENOMINATION IS ALREADY ADDED TO THIS WALLET
        throw_if(
            ($denominationData->type === "coin" 
                && array_key_exists($denominationData->name, $this->coins))
            || ($denominationData->type === "bill" 
                && array_key_exists($denominationData->name, $this->bills)),
            WalletDenominationAlreadyExistsException::class,
            $denominationData->name,
            $denominationData->type
        );

        $this->recordThat(new WalletDenominationAdded(
            walletId: $this->uuid(),
            denominationId: $denominationId,
            name: $denominationData->name,
            type: $denominationData->type
        ));

        return $this;
    }

    protected function applyWalletDenominationAdded(WalletDenominationAdded $event): void
    {
        if($event->type === 'coin') {
            $this->coins[] = $event->name;
        }

        if ($event->type === 'bill') {
            $this->bills[] = $event->name;
        }

        $this->denominationIds[] = $event->denominationId;
    }

    public function removeDenomination(RemoveWalletDenominationData $data,): self
    {
        throw_unless(in_array($data->denominationId, $this->denominationIds), UnknownDenominationException::class);

        throw_if($data->quantity > 0, WalletDenominationBalanceExistsException::class);

        $this->recordThat(new WalletDenominationRemoved(
            denominationId: $data->denominationId
        ));

        return $this;
    }
}
