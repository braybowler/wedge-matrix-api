<?php

namespace Feature\Services;

use App\Exceptions\CouldNotDeletePracticeSessionException;
use App\Models\PracticeSession;
use App\Models\User;
use App\Services\PracticeSession\PracticeSessionDeletionService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class PracticeSessionDeletionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_a_practice_session(): void
    {
        $user = User::factory()->create();
        $practiceSession = PracticeSession::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseCount('practice_sessions', 1);

        $service = app(PracticeSessionDeletionService::class);
        $service->delete($practiceSession);

        $this->assertDatabaseCount('practice_sessions', 0);
        $this->assertDatabaseMissing('practice_sessions', ['id' => $practiceSession->id]);
    }

    public function test_throws_could_not_delete_practice_session_exception_on_query_exception(): void
    {
        $practiceSession = Mockery::mock(PracticeSession::class);
        $practiceSession->shouldReceive('delete')
            ->once()
            ->andThrow(new QueryException('test', '', [], new Exception));

        Log::shouldReceive('error')->once()->with(
            'Failed to delete practice session',
            Mockery::any()
        );

        $this->expectException(CouldNotDeletePracticeSessionException::class);

        $service = app(PracticeSessionDeletionService::class);
        $service->delete($practiceSession);
    }

    public function test_rethrows_generic_exceptions(): void
    {
        $practiceSession = Mockery::mock(PracticeSession::class);
        $practiceSession->shouldReceive('delete')
            ->once()
            ->andThrow(new Exception('Something went wrong'));

        Log::shouldReceive('error')->once()->with(
            'Server error while deleting practice session',
            Mockery::any()
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong');

        $service = app(PracticeSessionDeletionService::class);
        $service->delete($practiceSession);
    }
}
