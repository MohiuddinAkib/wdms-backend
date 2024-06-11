<?php

namespace Tests\Feature\Domain\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogoutUserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_needs_to_be_authenticated_to_logout(): void
    {
        $response = $this->postJson(route('auth.logout'));

        $response->assertUnauthorized();
    }

    public function test_user_can_logout_from_frontend_spa(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, guard: 'web');

        $response = $this->postJson(route('auth.logout'), headers: [
            'referer' => 'localhost'
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful.'
            ]);
        $this->assertFalse($this->isAuthenticated('web'));
    }

    public function test_user_can_logout_from_traditional_web(): void
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Sanctum::actingAs($user, guard: 'web');

        $response = $this->post(route('auth.logout'), headers: [
            'origin' => 'localhost'
        ]);

        $response->assertRedirect();
        $this->assertFalse($this->isAuthenticated('web'));
    }

    public function test_user_can_logout_from_api_client(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson(route('auth.logout'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful.'
            ]);

    }
}
