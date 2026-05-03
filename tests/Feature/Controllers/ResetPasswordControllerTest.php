<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_resets_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword1!'),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route('password.reset'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Your password has been reset.');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword1!', $user->password));
    }

    public function test_revokes_all_sanctum_tokens_on_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword1!'),
        ]);

        $user->createToken('wedge-matrix');
        $user->createToken('wedge-matrix');
        $this->assertCount(2, $user->tokens);

        $token = Password::createToken($user);

        $this->postJson(route('password.reset'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    public function test_returns_400_for_invalid_token(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword1!'),
        ]);

        $response = $this->postJson(route('password.reset'), [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('message', 'Invalid or expired reset token.');
    }

    public function test_returns_400_for_expired_token(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('OldPassword1!'),
        ]);

        $token = Password::createToken($user);

        $this->travel(61)->minutes();

        $response = $this->postJson(route('password.reset'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('message', 'Invalid or expired reset token.');
    }

    public function test_returns_400_for_non_existent_email(): void
    {
        $response = $this->postJson(route('password.reset'), [
            'token' => 'some-token',
            'email' => 'nonexistent@example.com',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('message', 'Invalid or expired reset token.');
    }

    public function test_returns_validation_error_when_fields_are_missing(): void
    {
        $response = $this->postJson(route('password.reset'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    public function test_returns_validation_error_for_weak_password(): void
    {
        $response = $this->postJson(route('password.reset'), [
            'token' => 'some-token',
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('password');
    }

    public function test_returns_validation_error_for_unconfirmed_password(): void
    {
        $response = $this->postJson(route('password.reset'), [
            'token' => 'some-token',
            'email' => 'test@example.com',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'DifferentPassword1!',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('password');
    }

    public function test_returns_500_on_server_error(): void
    {
        Password::shouldReceive('reset')->once()->andThrow(new Exception);

        $response = $this->postJson(route('password.reset'), [
            'token' => 'some-token',
            'email' => 'test@example.com',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected error while resetting password');
    }
}
