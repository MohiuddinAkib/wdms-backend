<?php

namespace Tests\Feature\Domain\Denomination;

use Tests\TestCase;

class DenominationControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
