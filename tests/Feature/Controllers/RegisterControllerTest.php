<?php

namespace Feature\Controllers;

use Facades\App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
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
                'passwordConfirmation' => 'password',
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
                'passwordConfirmation' => 'password',
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
                'passwordConfirmation' => 'password',
            ],
            'duplicate email' => [
                'email' => 'test@example.com',
                'password' => 'password',
                'passwordConfirmation' => 'password',
            ],
            'invalid password' => [
                'email' => 'test@example.com',
                'password' => null,
                'passwordConfirmation' => null,
            ],
            'password and passwordConfirmation do not match'  => [
                'email' => 'test@example.com',
                'password' => 'test',
                'passwordConfirmation' => 'different',
            ],
        ];
    }

    #[DataProvider('requestProvider')]
    public function test_responds_with_a_json_payload_when_registration_request_fails_validation($email, $password, $passwordConfirmation): void
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
                'passwordConfirmation' => $passwordConfirmation,
            ]
        );

        $response->assertUnprocessable();
    }

    public function test_responds_with_a_json_payload_when_creating_user_fails_during_registration_request(): void
    {
        $exception = new QueryException(
            '',
            'insert into "users" ("email","password") values (?, ?)',
            ['test@example.com', 'password'],
            new Exception()
        );

        User::shouldReceive('create')
            ->once()
            ->andThrow($exception);

        $response = $this->postJson(
            route('register'),
            [
                'email' => 'test@example.com',
                'password' => 'password',
                'passwordConfirmation' => 'password',
            ]
        );

        $response
            ->assertStatus(400)
            ->assertJson(['message' => 'Could not create user']);
    }

    public function test_logs_error_messaging_when_creating_user_fails_during_registration_request(): void
    {
        $exception = new QueryException(
            '',
            'insert into "users" ("email","password") values (?, ?)',
            ['test@example.com', 'password'],
            new Exception()
        );

        User::shouldReceive('create')
            ->once()
            ->andThrow($exception);

        Log::shouldReceive('error')->once()->with(
            'Failed to create user while registering',
            Mockery::any()
        );

        $this->postJson(
            route('register'),
            [
                'email' => 'test@example.com',
                'password' => 'password',
                'passwordConfirmation' => 'password',
            ]
        );
    }

    public function test_responds_with_a_json_payload_when_catching_a_server_error_during_registration_request(): void
    {
        $exception = new Exception();

        User::shouldReceive('create')
            ->once()
            ->andThrow($exception);

        $response = $this->postJson(
            route('register'),
            [
                'email' => 'test@example.com',
                'password' => 'password',
                'passwordConfirmation' => 'password',
            ]
        );

        $response
            ->assertStatus(500)
            ->assertJson(['message' => 'Unexpected error while registering user']);
    }

    public function test_logs_error_messaging_when_catching_a_server_error_during_registration_request(): void
    {
        $exception = new Exception();

        User::shouldReceive('create')
            ->once()
            ->andThrow($exception);

        Log::shouldReceive('error')->once()->with(
            'Server error while registering user',
            Mockery::any()
        );

        $this->postJson(
            route('register'),
            [
                'email' => 'test@example.com',
                'password' => 'password',
                'passwordConfirmation' => 'password',
            ]
        );
    }
}
