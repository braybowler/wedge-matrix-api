<?php

namespace Feature\Services;

use App\Models\User;
use App\Services\UserCreationService;
use Facades\App\Models\WedgeMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_created_user_has_a_hashed_password(): void
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

    public function test_creates_a_related_wedge_matrix_for_the_newly_created_user(): void
    {
        $this->markTestIncomplete();
    }

    public function test_logs_error_messaging_when_catching_a_query_exception_during_user_creation(): void
    {
        $this->markTestIncomplete();
    }

    public function test_rethrows_when_catching_a_query_exception_during_user_creation(): void
    {
        $this->markTestIncomplete();
    }


    public function test_rethrows_when_catching_a_server_error_during_user_creation(): void
    {
        $this->markTestIncomplete();
    }
}
