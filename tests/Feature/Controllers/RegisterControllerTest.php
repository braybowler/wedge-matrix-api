<?php

namespace Feature\Controllers;

use App\Exceptions\CouldNotCreateUserException;
use App\Services\User\UserCreationService;
use Exception;
use Facades\App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_user_on_successful_registration_request(): void
    {
        $response = $this->postJson(
            route('register'),
            [
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $response->assertCreated();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_responds_with_a_json_payload_on_successful_registration_request(): void
    {
        $response = $this->postJson(
            route('register'),
            [
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $this->assertDatabaseCount('users', 1);

        $response->assertCreated();
        $response->assertJsonPath('user.email', 'test@example.com');
        $response->assertJsonPath('message', 'User registered successfully');
    }

    public static function requestProvider(): array
    {
        return [
            'invalid email' => [
                'email' => 'test',
                'password' => 'password',
                'password_confirmation' => 'password',
            ],
            'duplicate email' => [
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ],
            'invalid password' => [
                'email' => 'test@example.com',
                'password' => null,
                'password_confirmation' => null,
            ],
            'password and password_confirmation do not match' => [
                'email' => 'test@example.com',
                'password' => 'test',
                'password_confirmation' => 'different',
            ],
        ];
    }

    #[DataProvider('requestProvider')]
    public function test_responds_with_a_json_payload_when_registration_request_fails_validation($email, $password, $password_confirmation): void
    {
        User::factory()->create([
            'email' => $email,
            'password' => 'password',
        ]);

        $response = $this->postJson(
            route('register'),
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password_confirmation,
            ]
        );

        $response->assertUnprocessable();
    }

    public function test_responds_with_a_json_payload_when_creating_user_fails_during_registration_request(): void
    {
        $this->mock(UserCreationService::class, function ($mock) {
            $mock->shouldReceive('create')
                ->once()
                ->with('test@example.com', 'password')
                ->andThrow(new CouldNotCreateUserException('Could not create user'));
        });

        $response = $this->postJson(route('register'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertBadRequest()
            ->assertJson(['message' => 'Could not create user']);
    }

    public function test_responds_with_a_json_payload_when_catching_a_server_error_during_registration_request(): void
    {
        $this->mock(UserCreationService::class, function ($mock) {
            $mock->shouldReceive('create')
                ->once()
                ->with('test@example.com', 'password')
                ->andThrow(new Exception);
        });

        $response = $this->postJson(
            route('register'),
            [
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $response
            ->assertStatus(500)
            ->assertJson(['message' => 'Unexpected error while registering user']);
    }
}
