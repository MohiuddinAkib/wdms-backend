<?php

namespace Tests\Feature\Domain\Wallet;

use App\Domain\Wallet\Dto\AddWalletDenominationData;
use App\Domain\Wallet\Projections\Denomination;
use App\Domain\Wallet\WalletAggregateRoot;
use App\Models\User;
use Cache;
use Database\Factories\DenominationFactory;
use Database\Factories\WalletFactory;
use Illuminate\Cache\TaggableStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Str;
use Tests\TestCase;

class RemoveWalletDenominationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_should_be_authenticated_to_delete_wallet_denomination(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()->withCurrency(
            'bdt'
        )
            ->withUserUuid($user->uuid)
            ->create();
        $denomination = DenominationFactory::new()
            ->withName('5 taka')
            ->withType('coin')
            ->withQuantity(6)
            ->withValue(5)
            ->withWalletUuid($wallet->getkey())
            ->create();

        $response = $this->deleteJson(route('wallet-denominations.destroy', [$wallet->getKey(), $denomination->getKey()]));

        $response->assertUnauthorized();
    }

    public function test_should_be_wallet_owner_to_delete_wallet_denomination(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $user2 = User::factory()->create();
        $wallet = WalletFactory::new()->withCurrency(
            'bdt'
        )
            ->withUserUuid($user2->uuid)
            ->create();

        $denomination = DenominationFactory::new()
            ->withName('5 taka')
            ->withType('coin')
            ->withQuantity(6)
            ->withValue(5)
            ->withWalletUuid($wallet->getkey())
            ->create();

        $response = $this->deleteJson(route('wallet-denominations.destroy', [$wallet->getKey(), $denomination->getKey()]));

        $response->assertNotFound();
    }

    public function test_should_denomination_belong_to_wallet_to_delete_wallet_denomination(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()->withCurrency(
            'bdt'
        )
            ->withUserUuid($user->uuid)
            ->create();
        DenominationFactory::new()
            ->withName('5 Taka')
            ->withType('coin')
            ->withQuantity(6)
            ->withValue(5)
            ->withWalletUuid($wallet->getkey())
            ->create();
        Sanctum::actingAs($user);

        $user2 = User::factory()->create();
        $wallet2 = WalletFactory::new()->withCurrency(
            'bdt'
        )
            ->withUserUuid($user2->uuid)
            ->create();
        $denomination = DenominationFactory::new()
            ->withName('5 Taka')
            ->withType('bill')
            ->withValue(5)
            ->withQuantity(6)
            ->withWalletUuid($wallet2->getkey())
            ->create();

        $response = $this->deleteJson(route('wallet-denominations.destroy', [$wallet->getKey(), $denomination->getKey()]));

        $response->assertNotFound();
    }

    public function test_should_have_zero_quantity_to_delete_wallet_denomination(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet = WalletFactory::new()->withCurrency(
            'bdt'
        )
            ->withUserUuid($user->uuid)
            ->create();

        $denomination = DenominationFactory::new()
            ->withName('5 taka')
            ->withType('coin')
            ->withQuantity(6)
            ->withValue(5)
            ->withWalletUuid($wallet->getkey())
            ->create();

        $response = $this->deleteJson(route('wallet-denominations.destroy', [$wallet->getKey(), $denomination->getKey()]));

        $response
            ->assertForbidden()
            ->assertJson([
                'message' => 'The denomination balance is not empty.',
            ]);

        $this->assertModelExists($denomination);
    }

    public function test_should_delete_wallet_denomination(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet = WalletFactory::new()->withCurrency(
            'bdt'
        )
            ->withUserUuid($user->uuid)
            ->create();

        $denominationId = (string) Str::uuid();

        DenominationFactory::new()
            ->withWalletUuid($wallet->getKey())
            ->withDenominationUUid($denominationId)
            ->withName(
                '5 Poisha'
            )
            ->withValue(
                0.05
            )
            ->withType(
                'coin'
            )
            ->withQuantity(0)
            ->create();

        $mockTaggable = Mockery::mock(TaggableStore::class);
        Cache::shouldReceive('tags')
            ->with(['wallets', $user->getKey()])
            ->once()
            ->andReturn($mockTaggable);
        $mockTaggable->shouldReceive('flush')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $response = $this->deleteJson(route('wallet-denominations.destroy', [$wallet->getKey(), $denominationId]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Denomination removed successfully',
            ]);

        $this->assertDatabaseMissing(Denomination::getModel()->getTable(), [
            'uuid' => $denominationId,
            'name' => '5 Poisha',
            'type' => 'coin',
        ]);
    }
}