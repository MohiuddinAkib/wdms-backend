<?php

namespace Tests\Feature\Domain\Wallet;

use App\Domain\Wallet\Projections\Wallet;
use App\Models\User;
use Database\Factories\DenominationFactory;
use Database\Factories\WalletFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetWalletDetailsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_should_be_authenticated_to_see_wallet_details(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)->create();
        $response = $this->getJson(route('wallets.show', $wallet->getKey()));

        $response->assertUnauthorized();
    }

    public function test_should_be_owner_to_be_able_to_see_wallet_details(): void
    {
        $owner = User::factory()->create();
        /** @var Wallet */
        $wallet = WalletFactory::new()->withUserUuid($owner->uuid)->create();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson(route('wallets.show', $wallet->getKey()));

        $response->assertNotFound();
    }

    public function test_should_be_able_to_see_wallet_details(): void
    {
        $user = User::factory()->create();
        /** @var Wallet */
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)->create();

        $denomination1 = DenominationFactory::new()
            ->withWalletUuid($wallet->getKey())
            ->withName('1 Poisha')
            ->withType('coin')
            ->withQuantity(5)
            ->withValue(0.01)
            ->create();

        $denomination2 = DenominationFactory::new()
            ->withWalletUuid($wallet->getKey())
            ->withName('5 Taka')
            ->withType('bill')
            ->withQuantity(2)
            ->withValue(5)
            ->create();

        Sanctum::actingAs($user);
        $response = $this->getJson(route('wallets.show', $wallet->getKey()));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $wallet->getKey(),
                    'balance' => '0.00',
                    'currency' => $wallet->currency,
                    'denominations' => [
                        [
                            'id' => $denomination1->getKey(),
                            'name' => $denomination1->name,
                            'type' => $denomination1->type,
                            'quantity' => $denomination1->quantity,
                        ],
                        [
                            'id' => $denomination2->getKey(),
                            'name' => $denomination2->name,
                            'type' => $denomination2->type,
                            'quantity' => $denomination2->quantity,
                        ],
                    ],
                ],
            ]);
    }
}
