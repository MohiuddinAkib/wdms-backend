<?php

namespace Tests\Feature\Domain\Transaction;

use App\Domain\Wallet\Dto\AddMoneyTransactionData;
use App\Domain\Wallet\Dto\AddMoneyTransactionItemData;
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

class WithdrawMoneyFromWalletTransactionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_should_be_authenticated_to_withdraw_money(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withBalance('0')
            ->withCurrency('bdt')
            ->create();

        $response = $this->postJson(route('transactions.withdraw', $wallet->getKey()), [

        ]);

        $response->assertUnauthorized();
    }

    public function test_should_wallet_owner_to_withdraw_money(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withBalance('0')
            ->withCurrency('bdt')
            ->create();

        $user2 = User::factory()->create();
        Sanctum::actingAs($user2);

        $response = $this->postJson(route('transactions.withdraw', $wallet->getKey()), [

        ]);

        $response->assertNotFound();
    }

    public function test_should_have_enough_wallet_balance_to_withdraw_money(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withBalance('0')
            ->withCurrency('bdt')
            ->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('transactions.withdraw', $wallet->getKey()), [

        ]);

        $response->assertForbidden()
            ->assertJson([
                'message' => 'Not enough balance.',
            ]);
    }

    public function test_should_provide_required_data_to_withdraw_money(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withBalance('23.55')
            ->withCurrency('bdt')
            ->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('transactions.withdraw', $wallet->getKey()), [
            'denominations' => [
                [

                ],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'denominations.0.denomination_id',
                'denominations.0.quantity',
            ]);
    }

    public function test_should_provide_valid_wallet_denomination_to_withdraw_money(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withBalance('25.55')
            ->withCurrency('bdt')
            ->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('transactions.withdraw', $wallet->getKey()), [
            'denominations' => [
                [
                    'denomination_id' => (string) Str::uuid(),
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'denominations.0.denomination_id',
            ]);
    }

    public function test_should_have_wallet_denomination_balance_to_withdraw_money(): void
    {
        $user = User::factory()->create();
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withBalance('125')
            ->withCurrency('bdt')
            ->create();

        $denominationId1 = (string) Str::uuid();
        $denominationId2 = (string) Str::uuid();
        $denominationId3 = (string) Str::uuid();

        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId1,
                '5 taka',
                5,
                'bill'
            ))
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId2,
                '10 taka',
                10,
                'bill'
            ))
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId3,
                '5 poisha',
                0.05,
                'coin'
            ))
            ->persist();

        $transactionGrpId = (string) Str::uuid();
        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addMoney(new AddMoneyTransactionData(
                $wallet->getKey(),
                [
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        $transactionGrpId,
                        $denominationId1,
                        'bill',
                        5,
                        5
                    ),
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        $transactionGrpId,
                        $denominationId2,
                        'bill',
                        10,
                        10
                    ),
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        $transactionGrpId,
                        $denominationId3,
                        'coin',
                        0.05,
                        100
                    ),
                ]
            ))
            ->persist();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('transactions.withdraw', $wallet->getKey()), [
            'denominations' => [
                [
                    'denomination_id' => $denominationId1,
                    'quantity' => 15,
                ],
                [
                    'denomination_id' => $denominationId2,
                    'quantity' => 10,
                ],
                [
                    'denomination_id' => $denominationId3,
                    'quantity' => 50,
                ],
                [
                    'denomination_id' => $denominationId3,
                    'quantity' => 15,
                ],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'denominations.0.quantity' => 'Not enough balance',
                'denominations.2.denomination_id' => 'The denominations.2.denomination_id field has a duplicate value.',
                'denominations.3.denomination_id' => 'The denominations.3.denomination_id field has a duplicate value.',
            ]);
    }

    public function test_should_reduce_wallet_balance_and_denomination_quantity_after_withdrawing_money(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withBalance('0')
            ->withCurrency('bdt')
            ->create();

        $denominationId1 = (string) Str::uuid();
        $denominationId2 = (string) Str::uuid();
        $denominationId3 = (string) Str::uuid();

        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId1,
                '5 taka',
                5,
                'bill'
            ))
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId2,
                '10 taka',
                10,
                'bill'
            ))
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId3,
                '5 poisha',
                0.05,
                'coin'
            ))
            ->persist();

        $transactionGrpId = (string) Str::uuid();
        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addMoney(
                new AddMoneyTransactionData(
                    $wallet->getKey(),
                    [
                        new AddMoneyTransactionItemData(
                            (string) Str::uuid(),
                            $transactionGrpId,
                            $denominationId1,
                            'bill',
                            5,
                            5
                        ),
                        new AddMoneyTransactionItemData(
                            (string) Str::uuid(),
                            $transactionGrpId,
                            $denominationId2,
                            'bill',
                            10,
                            10
                        ),
                        new AddMoneyTransactionItemData(
                            (string) Str::uuid(),
                            $transactionGrpId,
                            $denominationId3,
                            'coin',
                            0.05,
                            100
                        ),
                    ]
                )
            )
            ->persist();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('transactions.withdraw', $wallet->getKey()), [
            'denominations' => [
                [
                    'denomination_id' => $denominationId1,
                    'quantity' => 5,
                ],
                [
                    'denomination_id' => $denominationId2,
                    'quantity' => 10,
                ],
                [
                    'denomination_id' => $denominationId3,
                    'quantity' => 100,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Withdraw successful.',
                'data' => [
                    'id' => $wallet->getKey(),
                    'currency' => $wallet->currency,
                    'balance' => '0.00',
                ],
            ]);

        $response->assertJsonFragment([
            'id' => $denominationId1,
            'type' => 'bill',
            'quantity' => 0,
        ]);

        $response->assertJsonFragment([
            'id' => $denominationId2,
            'type' => 'bill',
            'quantity' => 0,
        ]);

        $response->assertJsonFragment([
            'id' => $denominationId3,
            'type' => 'coin',
            'quantity' => 0,
        ]);

        $this->assertDatabaseHas(Transaction::class, [
            'type' => 'withdraw',
        ]);
    }
}
