<?php

namespace Feature\Controllers\User;

use App\Exceptions\CouldNotDeleteUserException;
use App\Mail\AccountDeletionMail;
use App\Models\User;
use App\Services\User\UserDeletionService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_responds_with_no_content_on_successful_delete_request(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson(route('user.destroy'));

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_sends_account_deletion_email_on_successful_delete(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->deleteJson(route('user.destroy'));

        Mail::assertSent(AccountDeletionMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_disallows_guest_access_for_delete_requests(): void
    {
        $response = $this->deleteJson(route('user.destroy'));

        $response->assertUnauthorized();
    }

    public function test_responds_with_bad_request_when_catching_a_deletion_error(): void
    {
        $user = User::factory()->create();

        $this->mock(UserDeletionService::class, function ($mock) {
            $mock->shouldReceive('delete')->andThrow(new CouldNotDeleteUserException);
        });

        $response = $this->actingAs($user)->deleteJson(route('user.destroy'));

        $response->assertBadRequest();
        $response->assertJsonPath('message', 'Could not delete user');
    }

    public function test_responds_with_server_error_when_catching_a_generic_exception(): void
    {
        $user = User::factory()->create();

        $this->mock(UserDeletionService::class, function ($mock) {
            $mock->shouldReceive('delete')->andThrow(new Exception);
        });

        $response = $this->actingAs($user)->deleteJson(route('user.destroy'));

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected server error while deleting user');
    }
}
