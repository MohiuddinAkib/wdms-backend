<?php

namespace Database\Factories;

use App\Domain\Wallet\Dto\AddMoneyTransactionData;
use App\Domain\Wallet\Dto\AddMoneyTransactionItemData;
use App\Domain\Wallet\Dto\AddWalletDenominationData;
use App\Domain\Wallet\Projections\Denomination;
use App\Domain\Wallet\WalletAggregateRoot;
use Faker\Generator;
use Illuminate\Container\Container;
use Str;

class DenominationFactory
{
    private Generator $faker;

    public function __construct(
        private readonly array $attributes = []
    ) {
        $this->faker = Container::getInstance()->make(Generator::class);
    }

    private function state(array $attributes = []): self
    {
        return new self(array_merge(
            $this->attributes,
            $attributes
        ));
    }

    public static function new(array $attributes = []): self
    {
        return (new static())->state($attributes);
    }

    public function create(array $extra = [])
    {
        $state = array_merge(
            [
                'uuid' => $this->faker->uuid(),
            ],
            $this->attributes,
            $extra
        );

        $aggregate = WalletAggregateRoot::retrieve($state['wallet_id'])
            ->addWalletDenomination(new AddWalletDenominationData(
                $state['uuid'],
                $state['name'],
                $state['value'],
                $state['type']
            ));

        if (data_get($state, 'quantity', 0) > 0) {
            $aggregate->addMoney(new AddMoneyTransactionData(
                $state['wallet_id'],
                [
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $state['uuid'],
                        $state['type'],
                        $state['value'],
                        $state['quantity']
                    ),
                ]
            ));
        }

        $aggregate->persist();

        return Denomination::find($state['uuid']);
    }

    public function withWalletUuid(string $walletUuid): self
    {
        return $this->state([
            'wallet_id' => $walletUuid,
        ]);
    }

    public function withDenominationUUid(string $uuid): self
    {
        return $this->state([
            'uuid' => $uuid,
        ]);
    }

    public function withName(string $name): self
    {
        return $this->state([
            'name' => $name,
        ]);
    }

    public function withQuantity(int $qantity): self
    {
        return $this->state([
            'quantity' => $qantity,
        ]);
    }

    public function withType(string $type): self
    {
        return $this->state([
            'type' => $type,
        ]);
    }

    public function withValue(float $value): self
    {
        return $this->state([
            'value' => $value,
        ]);
    }
}
