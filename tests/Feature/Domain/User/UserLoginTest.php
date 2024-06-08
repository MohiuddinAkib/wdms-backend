<?php

namespace Tests\Feature\Domain\User;

use Notification;
use Tests\TestCase;
use App\Models\User;
use Tzsk\Otp\Facades\Otp;
use App\Domain\Auth\Dto\RequestOtpData;
use App\Notifications\LoginOtpNotification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    
    public function test_should_provide_email_and_password_for_user_login(): void
    {
        $response = $this->postJson(route('auth.request-otp'), [

        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
                'password'
            ]);
    }

    public function test_should_get_error_on_invalid_credentials_for_user_login(): void
    {
        $response = $this->postJson(route('auth.request-otp'), [
            'email' => $this->faker->email(),
            'password' => $this->faker->password()
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => trans('auth.failed'),
            ]);

        $user = User::factory()->create();
        
        $response = $this->postJson(route('auth.request-otp'), [
            'email' => $user->email,
            'password' => $this->faker->password()
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => trans('auth.failed'),
            ]);
    }

    public function test_should_get_otp_mail_after_successful_user_login(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        
        $otp = $this->faker->randomNumber(6);
        
        Otp::shouldReceive('make')
            ->once()
            ->withArgs([$user->email])
            ->andReturn($otp);

        $dto = new RequestOtpData(
            email: $user->email,
            password: 'password'
        );

        $response = $this->postJson(route('auth.request-otp'), $dto->all());

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'An otp has been sent to your mail.'
            ]);

        Notification::assertSentTo($user,  function (LoginOtpNotification $notification, array $channels) use ($otp) {
            return $notification->otp === (string) $otp;
        });

        $this->assertGuest();
    }

    public function test_should_not_be_authenticated_after_otp_token_mismatch_for_user_login(): void
    {
        $user = User::factory()->create();
        Otp::make($user->email);

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'otp' => (string)$this->faker->randomNumber(6)
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'otp' => 'Otp mismatched',
            ]);
    }

    public function test_should_be_authenticated_after_otp_token_match_from_stateful_spa_for_user_login(): void
    {
        $user = User::factory()->create();
        $otp = Otp::make($user->email);

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'otp' => (string)$otp
        ],
        [
            'referer' => 'localhost'
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Login successful.',
            ])
            ->assertJsonMissing([
                'token'
            ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_should_get_access_token_after_otp_token_match_for_stateful_user_login(): void
    {
        $user = User::factory()->create();
        $otp = Otp::make($user->email);

        $response = $this->post(route('auth.login'), [
            'email' => $user->email,
            'otp' => (string)$otp
        ],
        [
            'referer' => 'localhost'
        ]);

        $response
            ->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_should_get_access_token_after_otp_token_match_for_stateless_user_login(): void
    {
        $user = User::factory()->create();
        $otp = Otp::make($user->email);

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'otp' => (string)$otp
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Login successful.',
            ])
            ->assertJsonStructure([
                'token'
            ]);
    }
}
