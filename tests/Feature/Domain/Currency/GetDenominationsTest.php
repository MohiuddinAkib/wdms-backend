<?php

namespace Tests\Feature\Domain\Currency;

use Tests\TestCase;

class GetDenominationsTest extends TestCase
{
    public function test_should_get_list_of_denominations_for_a_currency(): void
    {
        config()->set('wallet', [
            'currencies' => [
                'bdt' => [
                    'name' => 'Bangladeshi Taka',
                    'coins' => [
                        [
                            'name' => '1 Poisha',
                            'value' => 0.01
                        ],
                        [
                            'name' => '5 Poisha',
                            'value' => 0.05
                        ],
                        [
                            'name' => '10 Poisha',
                            'value' => 0.10
                        ],
                        [
                            'name' => '25 Poisha',
                            'value' => 0.25
                        ],
                        [
                            'name' => '50 Poisha',
                            'value' => 0.5
                        ],
                        [
                            'name' => '1 Taka',
                            'value' => 1
                        ],
                        [
                            'name' => '2 Taka',
                            'value' => 2
                        ],
                        [
                            'name' => '5 Taka',
                            'value' => 5
                        ]
                    ],
                    'bills' => [
                        [
                            'name' => '2 Taka',
                            'value' => 2
                        ],
                        [
                            'name' => '5 Taka',
                            'value' => 5
                        ],
                        [
                            'name' => '10 Taka',
                            'value' => 10
                        ],
                        [
                            'name' => '20 Taka',
                            'value' => 20
                        ],
                        [
                            'name' => '50 Taka',
                            'value' => 50
                        ],
                        [
                            'name' => '100 Taka',
                            'value' => 100
                        ],
                        [
                            'name' => '500 Taka',
                            'value' => 500
                        ],
                        [
                            'name' => '1000 Taka',
                            'value' => 1000
                        ],
                        [
                            'name' => '1000 Taka',
                            'value' => 1000
                        ]
                    ]
                ],
                'usd' => [
                    'name' => 'United States dollar',
                ],
            ],
        ]);

        $response = $this->getJson(route('denomination.index', 'bdt'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'name' => '1 Poisha',
                        'value' => 0.01,
                        'type' => 'coin'
                    ],
                    [
                        'name' => '5 Poisha',
                        'value' => 0.05,
                        'type' => 'coin'
                    ],
                    [
                        'name' => '10 Poisha',
                        'value' => 0.10,
                        'type' => 'coin'
                    ],
                    [
                        'name' => '25 Poisha',
                        'value' => 0.25,
                        'type' => 'coin'
                    ],
                    [
                        'name' => '50 Poisha',
                        'value' => 0.5,
                        'type' => 'coin'
                    ],
                    [
                        'name' => '1 Taka',
                        'value' => 1,
                        'type' => 'coin'
                    ],
                    [
                        'name' => '2 Taka',
                        'value' => 2,
                        'type' => 'coin'
                    ],
                    [
                        'name' => '5 Taka',
                        'value' => 5,
                        'type' => 'coin'
                    ],
                    [
                        'name' => '2 Taka',
                        'value' => 2,
                        'type' => 'bill'
                    ],
                    [
                        'name' => '5 Taka',
                        'value' => 5,
                        'type' => 'bill'
                    ],
                    [
                        'name' => '10 Taka',
                        'value' => 10,
                        'type' => 'bill'
                    ],
                    [
                        'name' => '20 Taka',
                        'value' => 20,
                        'type' => 'bill'
                    ],
                    [
                        'name' => '50 Taka',
                        'value' => 50,
                        'type' => 'bill'
                    ],
                    [
                        'name' => '100 Taka',
                        'value' => 100,
                        'type' => 'bill'
                    ],
                    [
                        'name' => '500 Taka',
                        'value' => 500,
                        'type' => 'bill'
                    ],
                    [
                        'name' => '1000 Taka',
                        'value' => 1000,
                        'type' => 'bill'
                    ]
                ]
            ]);
    }
}
