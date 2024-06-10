<?php

namespace Tests\Feature\Domain\Wallet;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddDenominationToWalletTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_should_be_authenticated_to_be_able_to_add_denomination_to_wallet(): void
    {
        $response = $this->postJson(route('wallet.denomination')[

        ]);

        $response->assertUnauthorized();
    }
}
