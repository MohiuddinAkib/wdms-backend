<?php

namespace Tests\Feature\Domain\Wallet;

use App\Domain\Wallet\Exceptions\WalletBalanceNotEmptyException;
use Tests\TestCase;
use App\Models\User;
use Database\Factories\WalletFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Laravel\Sanctum\Sanctum;

class DeleteWalletTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_should_be_authenticated_to_delete_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)->create();
        $response = $this->deleteJson(route('wallet.destroy', $wallet->getKey()));

        $response->assertUnauthorized();
    }

    public function test_should_be_owner_to_delete_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $ownerUser = User::factory()->create();
        $wallet = WalletFactory::new()->withUserUuid($ownerUser->uuid)->create();

        $response = $this->deleteJson(route('wallet.destroy', $wallet->getKey()));
        $response->assertNotFound();
    }

    public function test_should_have_zero_balance_to_delete_wallet(): void
    {
        Exceptions::fake();
        $user = User::factory()->create();
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)->create([
            'balance' => $this->faker->randomNumber(nbDigits: 2)
        ]);
        Sanctum::actingAs($user);
        
        $response = $this->deleteJson(route('wallet.destroy', $wallet->getKey()));
        $response->assertBadRequest()
            ->assertJson([
                'success' => false,
                'message' => 'Wallet balance is not empty.',
            ]);

        Exceptions::assertReported(WalletBalanceNotEmptyException::class);
    }

    public function test_should_delete_zero_balanced_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)->create();
        Sanctum::actingAs($user);
        
        $response = $this->deleteJson(route('wallet.destroy', $wallet->getKey()));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Wallet deleted successfully.',
            ]);

        $this->assertModelMissing($wallet);
    }
}
