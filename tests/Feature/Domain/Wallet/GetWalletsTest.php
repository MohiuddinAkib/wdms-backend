<?php

namespace Tests\Feature\Domain\Wallet;

use App\Models\User;
use Database\Factories\WalletFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetWalletsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_should_be_authenticated_to_be_able_to_see_wallet_list(): void
    {
        $response = $this->getJson(route('wallets.index'));

        $response->assertUnauthorized();
    }

    public function test_should_be_able_to_see_owning_wallet_list(): void
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet1 = WalletFactory::new()->withUserUuid($user->uuid)->withCurrency('bdt')->create();
        $wallet2 = WalletFactory::new()->withUserUuid($user->uuid)->withCurrency('inr')->create();

        $anotherUser = User::factory()->create();
        $wallet3 = WalletFactory::new()->withUserUuid($anotherUser->uuid)->withCurrency('usd')->create();

        $response = $this->getJson(route('wallets.index'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'id' => $wallet1->getKey(),
                        'currency' => 'bdt',
                    ],
                    [
                        'id' => $wallet2->getKey(),
                        'currency' => 'inr',
                    ],
                ],
            ]);

        $response->assertJsonMissing([
            'id' => $wallet3->getKey(),
            'currency' => 'usd',
        ]);
    }
}
