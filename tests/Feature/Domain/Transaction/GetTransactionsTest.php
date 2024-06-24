<?php

namespace Tests\Feature\Domain\Transaction;

use App\Domain\Wallet\Dto\AddMoneyTransactionData;
use App\Domain\Wallet\Dto\AddMoneyTransactionItemData;
use App\Domain\Wallet\Dto\AddWalletDenominationData;
use App\Domain\Wallet\Dto\WithdrawMoneyTransactionData;
use App\Domain\Wallet\Dto\WithdrawMoneyTransactionItemData;
use App\Domain\Wallet\Projections\Wallet;
use App\Domain\Wallet\WalletAggregateRoot;
use App\Models\User;
use Database\Factories\WalletFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Str;
use Tests\TestCase;

class GetTransactionsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_should_be_authenticated_to_see_transaction_list(): void
    {
        $response = $this->getJson(route('transactions.index'));

        $response->assertUnauthorized();
    }

    public function test_should_see_own_transaction_list(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        /** @var Wallet */
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withBalance('0.00')
            ->withCurrency('bdt')
            ->create();

        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId1 = (string) Str::uuid(),
                '100 Taka',
                100,
                'bill'
            ))
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId2 = (string) Str::uuid(),
                '20 Taka',
                20,
                'bill'
            ))
            ->addMoney(new AddMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId1,
                        '100 Taka',
                        'bill',
                        100,
                        100
                    ),
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId2,
                        '20 Taka',
                        'bill',
                        20,
                        10
                    ),
                ]
            ))
            ->addMoney(new AddMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId1,
                        '100 Taka',
                        'bill',
                        100,
                        12
                    ),
                ]
            ))
            ->addMoney(new AddMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId2,
                        '20 Taka',
                        'bill',
                        20,
                        12
                    ),
                ]
            ))
            ->withdrawMoney(new WithdrawMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new WithdrawMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId1,
                        '100 Taka',
                        'bill',
                        100,
                        55
                    ),
                ]
            ))
            ->persist();

        $response = $this->getJson(route('transactions.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'wallet_id' => $wallet->getKey(),
                        'wallet_currency' => $wallet->currency,
                    ],
                ],
            ]);
    }

    public function test_should_filter_transaction_list_by_happened_at(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        /** @var Wallet */
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withBalance('0.00')
            ->withCurrency('bdt')
            ->create();

        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId1 = (string) Str::uuid(),
                '100 Taka',
                100,
                'bill'
            ))
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId2 = (string) Str::uuid(),
                '20 Taka',
                20,
                'bill'
            ))
            ->addMoney(new AddMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId1,
                        '100 Taka',
                        'bill',
                        100,
                        100
                    ),
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId2,
                        '20 Taka',
                        'bill',
                        20,
                        10
                    ),
                ]
            ))
            ->addMoney(new AddMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId1,
                        '100 Taka',
                        'bill',
                        100,
                        12
                    ),
                ]
            ))
            ->addMoney(new AddMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId2,
                        '20 Taka',
                        'bill',
                        20,
                        12
                    ),
                ]
            ))
            ->withdrawMoney(new WithdrawMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new WithdrawMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId1,
                        '100 Taka',
                        'bill',
                        100,
                        55
                    ),
                ]
            ))
            ->persist();

        $response = $this->getJson(route('transactions.index', [
            'filter' => [
                'happened_at_between' => now()->subMinutes(1).','.now(),
            ],
        ]));


        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'wallet_id' => $wallet->getKey(),
                        'wallet_currency' => $wallet->currency,
                    ],
                ],
            ]);
    }

    public function test_should_filter_transaction_list_by_transaction_type(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        /** @var Wallet */
        $wallet = WalletFactory::new()
            ->withUserUuid($user->uuid)
            ->withBalance('0.00')
            ->withCurrency('bdt')
            ->create();

        WalletAggregateRoot::retrieve($wallet->getKey())
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId1 = (string) Str::uuid(),
                '100 Taka',
                100,
                'bill'
            ))
            ->addWalletDenomination(new AddWalletDenominationData(
                $denominationId2 = (string) Str::uuid(),
                '20 Taka',
                20,
                'bill'
            ))
            ->addMoney(new AddMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId1,
                        '100 Taka',
                        'bill',
                        100,
                        100
                    ),
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId2,
                        '20 Taka',
                        'bill',
                        20,
                        10
                    ),
                ]
            ))
            ->addMoney(new AddMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId1,
                        '100 Taka',
                        'bill',
                        100,
                        12
                    ),
                ]
            ))
            ->addMoney(new AddMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new AddMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId2,
                        '20 Taka',
                        'bill',
                        20,
                        12
                    ),
                ]
            ))
            ->withdrawMoney(new WithdrawMoneyTransactionData(
                $wallet->getKey(),
                $wallet->currency,
                [
                    new WithdrawMoneyTransactionItemData(
                        (string) Str::uuid(),
                        (string) Str::uuid(),
                        $denominationId1,
                        '100 Taka',
                        'bill',
                        100,
                        55
                    ),
                ]
            ))
            ->persist();

        $response = $this->getJson(route('transactions.index', [
            'filter' => [
                'type' => 'withdraw',
            ],
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
