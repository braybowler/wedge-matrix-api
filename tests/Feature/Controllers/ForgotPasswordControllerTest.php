<?php

namespace Feature\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_200_when_email_exists(): void
    {
        Mail::fake();

        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson(route('password.forgot'), [
            'email' => 'test@example.com',
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'If an account exists with that email, you will receive a password reset link.');
    }

    public function test_returns_200_when_email_does_not_exist(): void
    {
        Mail::fake();

        $response = $this->postJson(route('password.forgot'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'If an account exists with that email, you will receive a password reset link.');
    }

    public function test_sends_password_reset_mail_when_user_exists(): void
    {
        Mail::fake();

        User::factory()->create(['email' => 'test@example.com']);

        $this->postJson(route('password.forgot'), [
            'email' => 'test@example.com',
        ]);

        Mail::assertSent(PasswordResetMail::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_does_not_send_mail_when_user_does_not_exist(): void
    {
        Mail::fake();

        $this->postJson(route('password.forgot'), [
            'email' => 'nonexistent@example.com',
        ]);

        Mail::assertNotSent(PasswordResetMail::class);
    }

    public function test_returns_validation_error_when_email_is_missing(): void
    {
        $response = $this->postJson(route('password.forgot'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
    }

    public function test_returns_validation_error_when_email_is_invalid(): void
    {
        $response = $this->postJson(route('password.forgot'), [
            'email' => 'not-an-email',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
    }

    public function test_returns_500_on_server_error(): void
    {
        Password::shouldReceive('sendResetLink')->once()->andThrow(new Exception);

        $response = $this->postJson(route('password.forgot'), [
            'email' => 'test@example.com',
        ]);

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected error while sending password reset link');
    }
}
