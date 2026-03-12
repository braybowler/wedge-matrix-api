<?php

namespace Feature\Services;

use App\Exceptions\CouldNotCreatePracticeSessionException;
use App\Models\PracticeSession;
use App\Models\User;
use App\Models\WedgeMatrix;
use App\Services\PracticeSession\PracticeSessionCreationService;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class PracticeSessionCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function validData(?int $matrixId = null): array
    {
        return [
            'wedge_matrix_id' => $matrixId,
            'shot_count' => 5,
            'shots' => [
                ['shot_number' => 1, 'target_yards' => 50, 'actual_carry' => 48.5, 'difference' => 1.5],
                ['shot_number' => 2, 'target_yards' => 75, 'actual_carry' => 72.0, 'difference' => 3.0],
                ['shot_number' => 3, 'target_yards' => 60, 'actual_carry' => 61.2, 'difference' => 1.2],
                ['shot_number' => 4, 'target_yards' => 90, 'actual_carry' => 85.0, 'difference' => 5.0],
                ['shot_number' => 5, 'target_yards' => 40, 'actual_carry' => 42.0, 'difference' => 2.0],
            ],
            'average_difference' => 2.5,
        ];
    }

    public function test_creates_a_practice_session(): void
    {
        $user = User::factory()->create();
        $matrix = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $service = app(PracticeSessionCreationService::class);
        $practiceSession = $service->create($user, $this->validData($matrix->id));

        $this->assertInstanceOf(PracticeSession::class, $practiceSession);
        $this->assertEquals($user->id, $practiceSession->user_id);
        $this->assertEquals($matrix->id, $practiceSession->wedge_matrix_id);
        $this->assertEquals(5, $practiceSession->shot_count);
        $this->assertDatabaseCount('practice_sessions', 1);
    }

    public function test_throws_could_not_create_practice_session_exception_on_query_exception(): void
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('create')
            ->once()
            ->andThrow(new QueryException('test', '', [], new Exception));

        $user = Mockery::mock(User::class);
        $user->shouldReceive('practiceSessions')->andReturn($hasMany);

        Log::shouldReceive('error')->once()->with(
            'Failed to create practice session',
            Mockery::any()
        );

        $this->expectException(CouldNotCreatePracticeSessionException::class);

        $service = app(PracticeSessionCreationService::class);
        $service->create($user, $this->validData());
    }

    public function test_rethrows_generic_exceptions(): void
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('create')
            ->once()
            ->andThrow(new Exception('Something went wrong'));

        $user = Mockery::mock(User::class);
        $user->shouldReceive('practiceSessions')->andReturn($hasMany);

        Log::shouldReceive('error')->once()->with(
            'Server error while creating practice session',
            Mockery::any()
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong');

        $service = app(PracticeSessionCreationService::class);
        $service->create($user, $this->validData());
    }
}
