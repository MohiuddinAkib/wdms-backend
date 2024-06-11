<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Dto\AddMoneyTransactionData;
use App\Domain\Wallet\Dto\AddWalletDenominationData;
use App\Domain\Wallet\Dto\RemoveWalletDenominationData;
use App\Domain\Wallet\Dto\WithdrawMoneyTransactionData;
use App\Domain\Wallet\Events\MoneyAdded;
use App\Domain\Wallet\Events\MoneyWithdrawn;
use App\Domain\Wallet\Events\WalletCreated;
use App\Domain\Wallet\Events\WalletDeleted;
use App\Domain\Wallet\Events\WalletDenominationAdded;
use App\Domain\Wallet\Events\WalletDenominationRemoved;
use App\Domain\Wallet\Exceptions\NotSufficientBalanceException;
use App\Domain\Wallet\Exceptions\UnknownDenominationException;
use App\Domain\Wallet\Exceptions\WalletAlreadyExistsException;
use App\Domain\Wallet\Exceptions\WalletBalanceNotEmptyException;
use App\Domain\Wallet\Exceptions\WalletDenominationAlreadyExistsException;
use App\Domain\Wallet\Exceptions\WalletDenominationBalanceExistsException;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class WalletAggregateRoot extends AggregateRoot
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
        // WON'T ALLOW TO CREATE DUPLICATE WALLET WITH SAME UID AND CURRENCY
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
        // WON'T ALLOW TO DELETE IF THERE IS BALANCE IN THE WALLET
        throw_if(
            BigDecimal::of($this->balance)->compareTo(0) > 0,
            WalletBalanceNotEmptyException::class
        );

        $this->recordThat(new WalletDeleted(
            walletId: $this->uuid()
        ));

        return $this;
    }

    public function addWalletDenomination(AddWalletDenominationData $denominationData): self
    {
        // CHECKING IF THE DENOMINATION IS ALREADY ADDED TO THIS WALLET
        throw_if(
            ($denominationData->type === 'coin'
                && array_key_exists($denominationData->name, $this->coins))
            || ($denominationData->type === 'bill'
                && array_key_exists($denominationData->name, $this->bills)),
            WalletDenominationAlreadyExistsException::class,
            $denominationData->name,
            $denominationData->type
        );

        $this->recordThat(new WalletDenominationAdded(
            walletId: $this->uuid(),
            denominationId: $denominationData->denominationId,
            name: $denominationData->name,
            value: $denominationData->value,
            type: $denominationData->type
        ));

        return $this;
    }

    protected function applyWalletDenominationAdded(WalletDenominationAdded $event): void
    {
        // TRACKING WHICH COINS ARE ADDED IN THE WALLET
        if ($event->type === 'coin') {
            $this->coins[] = $event->name;
        }

        // TRACKING WHICH BILLS ARE ADDED IN THE WALLET
        if ($event->type === 'bill') {
            $this->bills[] = $event->name;
        }

        // TRACKING ALL ADDED COINS AND BILLS ID
        $this->denominationIds[] = $event->denominationId;
    }

    public function removeDenomination(RemoveWalletDenominationData $data): self
    {
        // CHECKING IF THE PASSED DENOMINATION ID IS VALID TO REMOVE
        throw_unless(in_array($data->denominationId, $this->denominationIds), UnknownDenominationException::class);

        // CHECKING IF THE DENOMINATION HAS BALANCE
        throw_if($data->quantity > 0, WalletDenominationBalanceExistsException::class);
        $this->recordThat(new WalletDenominationRemoved(
            denominationId: $data->denominationId
        ));

        return $this;
    }

    public function addMoney(AddMoneyTransactionData $data): self
    {
        $this->recordThat(new MoneyAdded(
            walletId: $this->uuid(),
            transactionData: $data,
            happenedAt: now()
        ));

        return $this;
    }

    protected function applyMoneyAdded(MoneyAdded $event)
    {
        $this->balance = (string) BigDecimal::of($this->balance)
            ->plus($event->transactionData->total())
            ->toScale(2, RoundingMode::DOWN);
    }

    public function withdrawMoney(WithdrawMoneyTransactionData $data): self
    {
        throw_if(BigDecimal::of($this->balance)->compareTo(0) <= 0, NotSufficientBalanceException::class);

        $this->recordThat(new MoneyWithdrawn(
            walletId: $this->uuid(),
            transactionData: $data,
            happenedAt: now()
        ));

        return $this;
    }

    protected function applyMoneyWithdrawn(MoneyWithdrawn $event)
    {
        $this->balance = (string) BigDecimal::of($this->balance)
            ->minus($event->transactionData->total())
            ->toScale(2, RoundingMode::DOWN);
    }
}
