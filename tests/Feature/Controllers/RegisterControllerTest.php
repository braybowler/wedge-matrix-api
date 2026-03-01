<?php

namespace Feature\Controllers;

use App\Exceptions\CouldNotCreateUserException;
use App\Mail\WelcomeMail;
use App\Services\User\UserCreationService;
use Exception;
use Facades\App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'tos_accepted' => true,
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
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'tos_accepted' => true,
            ]
        );

        $this->assertDatabaseCount('users', 1);

        $response->assertCreated();
        $response->assertJsonPath('data.email', 'test@example.com');
        $response->assertJsonPath('message', 'User registered successfully');
        $response->assertJsonStructure([
            'data' => [
                'id',
                'email',
                'has_dismissed_tutorial',
                'wedge_matrices',
            ],
            'message',
        ]);
    }

    public function test_sends_welcome_email_on_successful_registration(): void
    {
        Mail::fake();

        $this->postJson(
            route('register'),
            [
                'email' => 'test@example.com',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'tos_accepted' => true,
            ]
        );

        Mail::assertSent(WelcomeMail::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public static function requestProvider(): array
    {
        return [
            'invalid email' => [
                'email' => 'test',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'tos_accepted' => true,
            ],
            'duplicate email' => [
                'email' => 'test@example.com',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'tos_accepted' => true,
            ],
            'invalid password' => [
                'email' => 'test@example.com',
                'password' => null,
                'password_confirmation' => null,
                'tos_accepted' => true,
            ],
            'password and password_confirmation do not match' => [
                'email' => 'test@example.com',
                'password' => 'test',
                'password_confirmation' => 'different',
                'tos_accepted' => true,
            ],
            'tos not accepted' => [
                'email' => 'new@example.com',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'tos_accepted' => false,
            ],
        ];
    }

    #[DataProvider('requestProvider')]
    public function test_responds_with_a_json_payload_when_registration_request_fails_validation($email, $password, $password_confirmation, $tos_accepted): void
    {
        User::factory()->create([
            'email' => $email,
            'password' => 'password',
        ]);

        $payload = [
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password_confirmation,
            'tos_accepted' => $tos_accepted,
        ];

        $response = $this->postJson(
            route('register'),
            $payload,
        );

        $response->assertUnprocessable();
    }

    public function test_responds_with_a_json_payload_when_creating_user_fails_during_registration_request(): void
    {
        $this->mock(UserCreationService::class, function ($mock) {
            $mock->shouldReceive('create')
                ->once()
                ->with('test@example.com', 'Password1!', true)
                ->andThrow(new CouldNotCreateUserException('Could not create user'));
        });

        $response = $this->postJson(route('register'), [
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'tos_accepted' => true,
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
                ->with('test@example.com', 'Password1!', true)
                ->andThrow(new Exception);
        });

        $response = $this->postJson(
            route('register'),
            [
                'email' => 'test@example.com',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'tos_accepted' => true,
            ]
        );

        $response
            ->assertStatus(500)
            ->assertJson(['message' => 'Unexpected error while registering user']);
    }
}
