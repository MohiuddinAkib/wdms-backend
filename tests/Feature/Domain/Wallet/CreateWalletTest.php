<?php

namespace Tests\Feature\Domain\Wallet;

use App\Domain\Wallet\Projections\Wallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class CreateWalletTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_should_be_authenticated_to_create_wallet(): void
    {
        $response = $this->postJson(route('wallets.store'), []);

        $response->assertUnauthorized();
    }

    public function test_should_provide_currency_to_create_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('wallets.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrorFor('currency');
    }

    public function test_should_provide_supported_currency_to_create_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        config()->set('wallet', [
            'currencies' => [
                'bdt' => [
                    'name' => 'Bangladeshi Taka',
                ],
                'usd' => [
                    'name' => 'United States dollar',
                ],
            ],
        ]);

        $response = $this->postJson(route('wallets.store'), [
            'currency' => $this->faker->randomAscii(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'currency' => 'Currency not supported',
            ]);
    }

    public function test_should_create_wallet_with_proper_data(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $mockTaggable = Mockery::mock(\Illuminate\Cache\TaggedCache::class);
        Cache::shouldReceive('tags')
            ->once()
            ->with(['wallets', $user->getKey()])
            ->andReturn($mockTaggable);
        $mockTaggable->shouldReceive('flush')
            ->once()
            ->withNoArgs()
            ->andReturnNull();

        config()->set('wallet', [
            'currencies' => [
                'bdt' => [
                    'name' => 'Bangladeshi Taka',
                ],
                'usd' => [
                    'name' => 'United States dollar',
                ],
            ],
        ]);

        $response = $this->postJson(route('wallets.store'), [
            'currency' => 'bdt',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Wallet created successfully',
                'data' => [
                    'currency' => 'bdt',
                    'balance' => 0,
                ],
            ]);
    }

    public function test_should_not_create_two_wallets_with_same_currency(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        config()->set('wallet', [
            'currencies' => [
                'bdt' => [
                    'name' => 'Bangladeshi Taka',
                ],
                'usd' => [
                    'name' => 'United States dollar',
                ],
            ],
        ]);

        $response = $this->postJson(route('wallets.store'), [
            'currency' => 'bdt',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Wallet created successfully',
                'data' => [
                    'currency' => 'bdt',
                    'balance' => 0,
                ],
            ]);

        $response = $this->postJson(route('wallets.store'), [
            'currency' => 'bdt',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['currency' => 'Wallet already exists with the currency: bdt']);

        $this->assertDatabaseCount(Wallet::getModel()->getTable(), 1);
    }
}
