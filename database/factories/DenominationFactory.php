<?php

namespace Database\Factories;

use App\Domain\Currency\Projections\Denomination;
use Faker\Generator;
use Illuminate\Container\Container;

class DenominationFactory
{
    private Generator $faker;

    public function __construct(
        private readonly array $attributes = []
    )
    {
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

        return Denomination::new()->writeable()->create($state);
    }

    public function withWalletUuid(string $walletUuid): self
    {
        return $this->state([
            'wallet_id' => $walletUuid,
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
}
