<?php

namespace Feature\Services;

use App\Exceptions\CouldNotCreateUserException;
use App\Models\User;
use App\Services\User\UserCreationService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class UserCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_user(): void
    {
        $email = 'test@example.com';
        $password = 'password';

        $service = app(UserCreationService::class);
        $service->create($email, $password);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_creates_a_user_with_a_hashed_password(): void
    {
        $email = 'test@example.com';
        $password = 'password';

        $service = app(UserCreationService::class);
        $service->create($email, $password);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);

        $user = User::first();
        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function test_sets_tos_accepted_at_on_newly_created_user(): void
    {
        $service = app(UserCreationService::class);
        $service->create('test@example.com', 'password');

        $user = User::first();
        $this->assertNotNull($user->tos_accepted_at);
    }

    public function test_creates_a_related_wedge_matrix_for_the_newly_created_user(): void
    {
        $email = 'test@example.com';
        $password = 'password';

        $service = app(UserCreationService::class);
        $service->create($email, $password);

        $user = User::first();
        $this->assertDatabaseCount('wedge_matrices', 1);
        $this->assertDatabaseHas('wedge_matrices', [
            'user_id' => $user->id,
        ]);
    }

    public function test_logs_error_messaging_when_catching_a_query_exception_during_user_creation(): void
    {
        $email = 'test@example.com';
        $password = 'password';
        User::factory()->create([
            'email' => $email,
            'password' => $password,
        ]);

        Log::shouldReceive('error')->once()->with(
            'Failed to create user while registering',
            Mockery::any()
        );
        $this->expectException(CouldNotCreateUserException::class);

        $service = app(UserCreationService::class);
        $service->create($email, $password);
    }

    public function test_rethrows_when_catching_a_query_exception_during_user_creation(): void
    {
        $email = 'test@example.com';
        $password = 'password';
        User::factory()->create([
            'email' => $email,
            'password' => $password,
        ]);

        $this->expectException(CouldNotCreateUserException::class);

        $service = app(UserCreationService::class);
        $service->create($email, $password);
    }

    public function test_logs_error_messaging_when_catching_a_server_error_during_user_creation(): void
    {
        $email = 'test@example.com';
        $password = 'password';

        Hash::shouldReceive('make')->once()->andThrow(new Exception);
        Log::shouldReceive('error')->once()->with(
            'Server error while registering user',
            Mockery::any()
        );

        $this->expectException(Exception::class);

        $service = app(UserCreationService::class);
        $service->create($email, $password);
    }

    public function test_rethrows_when_catching_a_server_error_during_user_creation(): void
    {
        $email = 'test@example.com';
        $password = 'password';

        Hash::shouldReceive('make')->once()->andThrow(new Exception);

        $this->expectException(Exception::class);

        $service = app(UserCreationService::class);
        $service->create($email, $password);
    }
}
