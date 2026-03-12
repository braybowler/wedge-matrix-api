<?php

namespace Feature\Controllers\PracticeSession;

use App\Exceptions\CouldNotDeletePracticeSessionException;
use App\Models\PracticeSession;
use App\Models\User;
use App\Services\PracticeSession\PracticeSessionDeletionService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_responds_with_no_content_on_successful_delete_request(): void
    {
        $user = User::factory()->create();
        $practiceSession = PracticeSession::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseCount('practice_sessions', 1);

        $response = $this->actingAs($user)->deleteJson(
            route('practice-session.destroy', $practiceSession)
        );

        $response->assertNoContent();
        $this->assertDatabaseCount('practice_sessions', 0);
        $this->assertDatabaseMissing('practice_sessions', ['id' => $practiceSession->id]);
    }

    public function test_disallows_guest_access_for_delete_requests(): void
    {
        $practiceSession = PracticeSession::factory()->create();

        $response = $this->deleteJson(
            route('practice-session.destroy', $practiceSession)
        );

        $response->assertUnauthorized();
    }

    public function test_disallows_access_to_other_resources(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $practiceSession = PracticeSession::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->deleteJson(
            route('practice-session.destroy', $practiceSession)
        );

        $response->assertForbidden();
    }

    public function test_responds_with_bad_request_when_catching_a_deletion_error(): void
    {
        $user = User::factory()->create();
        $practiceSession = PracticeSession::factory()->create(['user_id' => $user->id]);

        $this->mock(PracticeSessionDeletionService::class, function ($mock) {
            $mock->shouldReceive('delete')->andThrow(new CouldNotDeletePracticeSessionException);
        });

        $response = $this->actingAs($user)->deleteJson(
            route('practice-session.destroy', $practiceSession)
        );

        $response->assertBadRequest();
        $response->assertJsonPath('message', 'Could not delete practice session');
    }

    public function test_responds_with_server_error_when_catching_a_generic_exception(): void
    {
        $user = User::factory()->create();
        $practiceSession = PracticeSession::factory()->create(['user_id' => $user->id]);

        $this->mock(PracticeSessionDeletionService::class, function ($mock) {
            $mock->shouldReceive('delete')->andThrow(new Exception);
        });

        $response = $this->actingAs($user)->deleteJson(
            route('practice-session.destroy', $practiceSession)
        );

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected server error while deleting practice session');
    }
}
