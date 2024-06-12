<?php

namespace Database\Factories;

use App\Domain\Wallet\Projections\Wallet;
use App\Domain\Wallet\WalletAggregateRoot;
use Faker\Generator;
use Illuminate\Container\Container;

class WalletFactory
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
                'currency' => $this->faker->randomElement(['bdt', 'usd', 'inr']),
            ],
            $this->attributes,
            $extra
        );

        WalletAggregateRoot::retrieve($state['uuid'])
            ->createWallet($state['user_id'], $state['currency'])
            ->persist();

        /** @var Wallet */
        $wallet = Wallet::find($state['uuid']);

        if (array_key_exists('balance', $state)) {
            $wallet->writeable()->update([
                'balance' => $state['balance'],
            ]);
        }

        return $wallet;
    }

    public function withUserUuid(string $userUuid): self
    {
        return $this->state([
            'user_id' => $userUuid,
        ]);
    }

    public function withWalletUuid(string $walletUuid): self
    {
        return $this->state([
            'uuid' => $walletUuid,
        ]);
    }

    public function withCurrency(string $currency): self
    {
        return $this->state([
            'currency' => $currency,
        ]);
    }

    public function withBalance(float|int|string $balance): self
    {
        return $this->state([
            'balance' => $balance,
        ]);
    }
}
