<?php

namespace Tests\Feature\Domain\Wallet;

use App\Domain\Wallet\Projections\Denomination;
use App\Models\User;
use Cache;
use Database\Factories\DenominationFactory;
use Database\Factories\WalletFactory;
use Illuminate\Cache\TaggableStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class AddDenominationToWalletTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private function createWalletConfig()
    {
        config()->set('wallet', [
            'currencies' => [
                'bdt' => [
                    'name' => 'Bangladeshi Taka',
                    'coins' => [
                        [
                            'name' => '1 Poisha',
                            'value' => 0.01,
                        ],
                        [
                            'name' => '5 Poisha',
                            'value' => 0.05,
                        ],
                    ],
                    'bills' => [
                        [
                            'name' => '2 Taka',
                            'value' => 2,
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_should_be_authenticated_to_add_denomination_to_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)->create();

        $response = $this->postJson(route('wallet-denominations.store', $wallet->getKey()), [

        ]);

        $response->assertUnauthorized();
    }

    public function test_should_be_wallet_owner_to_add_denomination_to_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $owner = User::factory()->create();
        $wallet = WalletFactory::new()->withUserUuid($owner->uuid)->create();

        $response = $this->postJson(route('wallet-denominations.store', $wallet->getKey()), [

        ]);

        $response->assertNotFound();
    }

    public function test_should_provide_required_data_to_add_denomination_to_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)->create();

        $response = $this->postJson(route('wallet-denominations.store', $wallet->getKey()), [

        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'type',
                'value',
            ]);
    }

    public function test_should_provide_valid_denomination_type_to_add_denomination_to_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withCurrency('bdt')
            ->create();

        $response = $this->postJson(route('wallet-denominations.store', $wallet->getKey()), [
            'name' => '5 Taka',
            'type' => 'safasdf',
            'value' => 5,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'type' => 'The selected type is invalid.',
            ]);
    }

    public function test_should_provide_supported_denomination_to_add_denomination_to_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withCurrency('bdt')
            ->create();

        $this->createWalletConfig();

        $response = $this->postJson(route('wallet-denominations.store', $wallet->getKey()), [
            'name' => '5 Taka',
            'type' => 'coin',
            'value' => 5,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name' => 'Invalid denomination 5 Taka for currency bdt.',
            ]);
    }

    public function test_should_add_unique_denomination_to_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withCurrency('bdt')
            ->create();

        DenominationFactory::new()->withName('5 Poisha')
            ->withType('coin')
            ->withQuantity(5)
            ->withValue(0.05)
            ->withWalletUuid($wallet->getKey())
            ->create();

        $this->createWalletConfig();

        $response = $this->postJson(route('wallet-denominations.store', $wallet->getKey()), [
            'name' => '5 Poisha',
            'type' => 'coin',
            'value' => 0.05,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name' => 'The denomination has already been taken.',
            ]);
    }

    public function test_should_add_denomination_to_wallet(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withCurrency('bdt')
            ->create();

        $this->createWalletConfig();

        $mockTaggable = Mockery::mock(TaggableStore::class);
        Cache::shouldReceive('tags')
            ->with(['wallets', $user->getKey()])
            ->once()
            ->andReturn($mockTaggable);
        $mockTaggable->shouldReceive('flush')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $response = $this->postJson(route('wallet-denominations.store', $wallet->getKey()), [
            'name' => '5 Poisha',
            'type' => 'coin',
            'value' => 0.05,
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $wallet->getKey(),
                    'currency' => 'bdt',
                    'denominations' => [
                        [
                            'name' => '5 Poisha',
                            'type' => 'coin',
                            'value' => 0.05,
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseHas(Denomination::getModel()->getTable(), [
            'name' => '5 Poisha',
            'type' => 'coin',
            'value' => 0.05,
        ]);
    }
}
