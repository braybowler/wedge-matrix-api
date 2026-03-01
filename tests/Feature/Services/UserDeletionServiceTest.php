<?php

namespace Feature\Services;

use App\Exceptions\CouldNotDeleteUserException;
use App\Mail\AccountDeletionMail;
use App\Models\User;
use App\Models\WedgeMatrix;
use App\Services\User\UserDeletionService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class UserDeletionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_a_user(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $service = app(UserDeletionService::class);
        $service->delete($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_cascade_deletes_wedge_matrices(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseHas('wedge_matrices', ['id' => $wedgeMatrix->id]);

        $service = app(UserDeletionService::class);
        $service->delete($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('wedge_matrices', ['id' => $wedgeMatrix->id]);
    }

    public function test_deletes_user_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('wedge-matrix');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $service = app(UserDeletionService::class);
        $service->delete($user);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_sends_account_deletion_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $service = app(UserDeletionService::class);
        $service->delete($user);

        Mail::assertSent(AccountDeletionMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_throws_could_not_delete_user_exception_on_query_exception(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('email')->andReturn('test@example.com');
        $user->shouldReceive('tokens->delete')->once();
        $user->shouldReceive('delete')
            ->once()
            ->andThrow(new QueryException('test', '', [], new Exception));

        Log::shouldReceive('error')->once()->with(
            'Failed to delete user',
            Mockery::any()
        );

        $this->expectException(CouldNotDeleteUserException::class);

        $service = app(UserDeletionService::class);
        $service->delete($user);
    }

    public function test_rethrows_generic_exceptions(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('email')->andReturn('test@example.com');
        $user->shouldReceive('tokens->delete')->once();
        $user->shouldReceive('delete')
            ->once()
            ->andThrow(new Exception('Something went wrong'));

        Log::shouldReceive('error')->once()->with(
            'Server error while deleting user',
            Mockery::any()
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong');

        $service = app(UserDeletionService::class);
        $service->delete($user);
    }
}
