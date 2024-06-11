<?php

namespace Tests\Feature\Domain\Transaction;

use App\Domain\Wallet\Dto\AddWalletDenominationData;
use App\Domain\Wallet\Projections\Transaction;
use App\Domain\Wallet\WalletAggregateRoot;
use App\Models\User;
use Database\Factories\WalletFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Str;
use Tests\TestCase;

class AddMoneyToWalletTransactionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_should_be_authenticated_to_add_money_to_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)
            ->withCurrency('bdt')
            ->create();
        $denominationId = (string) Str::uuid();
        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                denominationId: $denominationId,
                name: '5 Taka',
                value: 5,
                type: 'bill'
            ))
            ->persist();

        $response = $this->postJson(route('transactions.store', $wallet->getKey()), [

        ]);

        $response
            ->assertUnauthorized();
    }

    public function test_should_be_wallet_owner_to_add_money_to_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $user2 = User::factory()->create();
        $wallet = WalletFactory::new()->withUserUuid($user2->uuid)
            ->withCurrency('bdt')
            ->create();
        $denominationId = (string) Str::uuid();
        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                denominationId: $denominationId,
                name: '5 Taka',
                value: 5,
                type: 'bill'
            ))
            ->persist();

        $response = $this->postJson(route('transactions.store', $wallet->getKey()), [

        ]);

        $response
            ->assertNotFound();
    }

    public function test_should_provide_required_data_to_add_money_to_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)
            ->withCurrency('bdt')
            ->create();
        $denominationId = (string) Str::uuid();
        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                denominationId: $denominationId,
                name: '5 Taka',
                value: 5,
                type: 'bill'
            ))
            ->persist();

        $response = $this->postJson(route('transactions.store', $wallet->getKey()), [
            'denominations' => [
                [
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'denominations.0.denomination_id',
                'denominations.0.quantity',
            ]);
    }

    public function test_should_provide_valid_quantity_to_add_money_to_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)
            ->withCurrency('bdt')
            ->create();
        $denominationId = (string) Str::uuid();
        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                denominationId: $denominationId,
                name: '5 Taka',
                value: 5,
                type: 'bill'
            ))
            ->persist();

        $response = $this->postJson(route('transactions.store', $wallet->getKey()), [
            'denominations' => [
                [
                    'denomination_id' => $denominationId,
                    'quantity' => 0,
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'denominations.0.quantity' => 'The denominations.0.quantity field must be at least 1.',
            ]);
    }

    public function test_should_provide_valid_wallet_denomination_to_add_money_to_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)
            ->withCurrency('bdt')
            ->create();
        $denominationId = (string) Str::uuid();
        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                denominationId: $denominationId,
                name: '5 Taka',
                value: 5,
                type: 'bill'
            ))
            ->persist();

        $response = $this->postJson(route('transactions.store', $wallet->getKey()), [
            'denominations' => [
                [
                    'denomination_id' => (string) Str::uuid(),
                    'quantity' => 5,
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'denominations.0.denomination_id' => 'The selected denominations.0.denomination id is invalid.',
            ]);
    }

    public function test_should_add_money_to_wallet(): void
    {
        config()->set('wallet', [
            'currencies' => [
                'bdt' => [
                    'coins' => [
                        [
                            'name' => '5 Poisha',
                            'value' => 0.05,
                        ],
                        [
                            'name' => '25 Poisha',
                            'value' => 0.25,
                        ],
                    ],
                    'bills' => [
                        [
                            'name' => '5 Taka',
                            'value' => 5,
                        ],
                    ],
                ],
            ],
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $wallet = WalletFactory::new()->withUserUuid($user->uuid)
            ->withCurrency('bdt')
            ->withBalance('9.99999999999999999999999999999999999999999999')
            ->create();
        $denominationId1 = (string) Str::uuid();
        $denominationId2 = (string) Str::uuid();
        $denominationId3 = (string) Str::uuid();
        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                denominationId: $denominationId1,
                name: '5 Taka',
                value: 5,
                type: 'bill'
            ))
            ->addWalletDenomination(new AddWalletDenominationData(
                denominationId: $denominationId2,
                name: '5 Poisha',
                value: 0.05,
                type: 'coin'
            ))
            ->addWalletDenomination(new AddWalletDenominationData(
                denominationId: $denominationId3,
                name: '25 Poisha',
                value: 0.25,
                type: 'coin'
            ))
            ->persist();

        $response = $this->postJson(route('transactions.store', $wallet->getKey()), [
            'denominations' => [
                [
                    'denomination_id' => $denominationId1,
                    'quantity' => 5,
                ],
                [
                    'denomination_id' => $denominationId2,
                    'quantity' => 1,
                ],
                [
                    'denomination_id' => $denominationId3,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Money added successfully.',
                'data' => [
                    'id' => $wallet->getKey(),
                    'currency' => 'bdt',
                    'balance' => '35.29',
                    'denominations' => [
                        [
                            'id' => $denominationId3,
                            'name' => '25 Poisha',
                            'value' => 0.25,
                            'type' => 'coin',
                        ],
                        [
                            'id' => $denominationId2,
                            'name' => '5 Poisha',
                            'value' => 0.05,
                            'type' => 'coin',
                        ],
                        [
                            'id' => $denominationId1,
                            'name' => '5 Taka',
                            'value' => 5,
                            'type' => 'bill',
                            'quantity' => 5,
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseCount(Transaction::getModel()->getTable(), 3);
    }
}
