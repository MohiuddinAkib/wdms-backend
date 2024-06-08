<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Domain\Auth\Dto\RegisterUserData;
use App\Domain\User\Events\UserRegistered;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_needs_to_provide_email_and_password(): void
    {
        $response = $this->postJson(route('auth.register'), [
            
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password'
            ]);
    }

    public function test_password_should_be_min_of_8_characters_long(): void
    {
        $dto = new RegisterUserData(
            name: $this->faker->name(),
            email: $this->faker->email(),
            password: "123456"
        );

        $response = $this->postJson(route('auth.register'), $dto->toArray());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'password' => 'The password field must be at least 8 characters',
            ]);
    }

    public function test_email_needs_to_be_unique(): void
    {
        $user = User::factory()->create();
        $dto = new RegisterUserData(
            name: $this->faker->name(),
            email: $user->email,
            password: $this->faker->password()
        );

        $response = $this->postJson(route('auth.register'), $dto->toArray());

        $response->dump();
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => 'The email has already been taken.',
            ]);
    }

    public function test_email_should_be_valid_email(): void
    {
        $dto = new RegisterUserData(
            name: $this->faker->name(),
            email: $this->faker->name(),
            password: $this->faker->password()
        );

        $response = $this->postJson(route('auth.register'), $dto->toArray());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => 'The email field must be a valid email address.',
            ]);
    }
    
    public function test_should_register_user(): void
    {
        $response = $this->postJson(route('auth.register'), [
            'name' => $name = $this->faker->name(),
            'email' => $email = $this->faker->email(),
            'password' => $this->faker->password(8)
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Registration successful',
            ]);
        
        /** @var EloquentStoredEvent */
        $eventModel = EloquentStoredEvent::query()
            ->whereEvent(UserRegistered::class)
            ->first();
        
        /** @var UserRegistered */
        $event = $eventModel->toStoredEvent()->event;

        $this->assertDatabaseCount(User::getModel()->getTable(), 1);
        $this->assertDatabaseHas(User::getModel()->getTable(), [
            'name' => $name,
            'email' => $email,
            'uuid' => $event->userId
        ]);
    }
}
