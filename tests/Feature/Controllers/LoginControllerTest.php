<?php

namespace Feature\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_responds_with_a_json_payload_on_successful_login_attempts(): void
    {
        $userEmail = 'test@example.com';
        User::factory()->create([
            'email' => $userEmail,
            'password' => Hash::make('password'),
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => $userEmail,
        ]);

        $response = $this->postJson(
            route('login'),
            [
                'email' => $userEmail,
                'password' => 'password',
            ]
        );

        $response->assertOk();
        $response->assertJsonPath('user.email', $userEmail);
        $response->assertJsonPath('message', 'Login successful');
    }

    public function test_responds_with_a_json_payload_on_unsuccessful_login_attempts(): void
    {
        $userEmail = 'test@example.com';
        User::factory()->create([
            'email' => $userEmail,
            'password' => Hash::make('password'),
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => $userEmail,
        ]);

        Auth::shouldReceive('attempt')->once()->andReturn(false);

        $response = $this->postJson(
            route('login'),
            [
                'email' => $userEmail,
                'password' => 'password',
            ]
        );

        $response->assertUnauthorized();
        $response->assertJsonPath('message', 'Invalid credentials');
    }

    public function test_logs_warning_messaging_on_unsuccessful_login_attempts(): void
    {
        $userEmail = 'test@example.com';
        $userPassword = Hash::make('password');
        User::factory()->create([
            'email' => $userEmail,
            'password' => $userPassword,
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => $userEmail,
        ]);

        Auth::shouldReceive('attempt')->once()->andReturn(false);
        Log::shouldReceive('warning')->once()->with(
            'Log in attempt with invalid credentials detected',
           [
               $userEmail,
           ]
        );

        $response = $this->postJson(
            route('login'),
            [
                'email' => $userEmail,
                'password' => 'password',
            ]
        );

        $response->assertUnauthorized();
        $response->assertJsonPath('message', 'Invalid credentials');
    }

    public static function requestProvider(): array
    {
        return [
            'invalid email' => [
                'email' => 'test',
                'password' => 'password',
            ],
            'invalid password' => [
                'email' => 'test@example.com',
                'password' => null,
            ],
        ];
    }

    #[DataProvider('requestProvider')]
    public function test_responds_with_a_json_payload_when_login_request_fails_validation($email, $password): void
    {
        User::factory()->create([
            'email' => $email,
            'password' => 'password',
        ]);

        $response = $this->postJson(
            route('login'),
            [
                'email' => $email,
                'password' => $password,
            ]
        );

        $response->assertUnprocessable();
    }

    public function test_responds_with_a_json_payload_when_catching_a_server_error_during_login_request(): void
    {
        $userEmail = 'test@example.com';
        User::factory()->create([
            'email' => $userEmail,
            'password' => Hash::make('password'),
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => $userEmail,
        ]);

        Auth::shouldReceive('attempt')->once()->andThrow(new Exception());

        $response = $this->postJson(
            route('login'),
            [
                'email' => $userEmail,
                'password' => 'password',
            ]
        );

        $response->assertServerError();
        $response->assertJsonPath(
            'message',
            'Unexpected error while logging in'
        );
    }

    public function test_logs_error_messaging_when_catching_a_server_error_during_login_request(): void
    {
        $userEmail = 'test@example.com';
        User::factory()->create([
            'email' => $userEmail,
            'password' => Hash::make('password'),
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => $userEmail,
        ]);

        Auth::shouldReceive('attempt')->once()->andThrow(new Exception());
        Log::shouldReceive('error')->with(
            'Server error while logging in',
            Mockery::any()
        );

        $this->postJson(
            route('login'),
            [
                'email' => $userEmail,
                'password' => 'password',
            ]
        );
    }
}
