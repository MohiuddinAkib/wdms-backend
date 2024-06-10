<?php

namespace Tests\Feature\Domain\Wallet;

use App\Domain\Wallet\Projections\Wallet;
use App\Models\User;
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
        Sanctum::actingAs($user);
        $response = $this->getJson(route('wallets.show', $wallet->getKey()));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'uuid' => $wallet->getKey(),
                    'balance' => '0.00',
                    'currency' => $wallet->currency,
                ]
            ]);
    }

    
}
