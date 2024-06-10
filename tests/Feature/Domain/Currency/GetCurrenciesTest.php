<?php

namespace Tests\Feature\Domain\Currency;

use Tests\TestCase;

class GetCurrenciesTest extends TestCase
{
    public function test_should_be_able_to_get_list_of_currencies(): void
    {
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

        $response = $this->getJson(route('currencies.index'));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'code' => 'bdt',
                        'name' => 'Bangladeshi Taka',
                    ],
                    [
                        'code' => 'usd',
                        'name' => 'United States dollar',
                    ],
                ],
            ]);
    }
}
