<?php

namespace Database\Factories;

use App\Domain\Wallet\Projections\Wallet;
use Faker\Generator;
use Illuminate\Container\Container;
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class WalletFactory
{
    private Generator $faker;

    public function __construct(private readonly array $attributes = [])
    {
        $this->faker = Container::getInstance()->make(Generator::class);
    }

    public static function new(array $attributes = []): self
    {
        return new static($attributes);
    }

    public function create(array $extra = [])
    {
        $state = array_merge(
            [
                'uuid' => (string) Str::uuid(),
                'currency' => $this->faker->randomElement(['bdt', 'usd', 'inr']),
            ],
            $this->attributes,
            $extra
        );

        return Wallet::new()->writeable()->create($state);
    }

    public function withUserUuid(string $userUuid): self
    {
        return self::new([
            'user_id' => $userUuid,
        ]);
    }

    public function withWalletUuid(string $walletUuid): self
    {
        return self::new([
            'uuid' => $walletUuid,
        ]);
    }
}
