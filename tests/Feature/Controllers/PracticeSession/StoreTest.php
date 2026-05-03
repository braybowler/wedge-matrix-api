<?php

namespace Tests\Feature\Controllers\PracticeSession;

use App\Exceptions\CouldNotCreatePracticeSessionException;
use App\Models\User;
use App\Models\WedgeMatrix;
use App\Services\PracticeSession\PracticeSessionCreationService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(int $userId, ?int $matrixId = null): array
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

    public function test_responds_with_created_on_successful_store_request(): void
    {
        $user = User::factory()->create();
        $matrix = WedgeMatrix::factory()->create(['user_id' => $user->id]);
        $payload = $this->validPayload($user->id, $matrix->id);

        $response = $this->actingAs($user)->postJson(
            route('practice-session.store'),
            $payload
        );

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'wedge_matrix_id',
                'shot_count',
                'shots',
                'average_difference',
                'created_at',
            ],
        ]);

        $this->assertDatabaseCount('practice_sessions', 1);
        $this->assertDatabaseHas('practice_sessions', [
            'user_id' => $user->id,
            'wedge_matrix_id' => $matrix->id,
            'shot_count' => 5,
        ]);
    }

    public function test_allows_null_wedge_matrix_id(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload($user->id);

        $response = $this->actingAs($user)->postJson(
            route('practice-session.store'),
            $payload
        );

        $response->assertCreated();
        $response->assertJsonPath('data.wedge_matrix_id', null);
    }

    public function test_disallows_guest_access_for_store_requests(): void
    {
        $response = $this->postJson(
            route('practice-session.store')
        );

        $response->assertUnauthorized();
    }

    public function test_validates_shot_count_must_be_valid(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload($user->id);
        $payload['shot_count'] = 7;

        $response = $this->actingAs($user)->postJson(
            route('practice-session.store'),
            $payload
        );

        $response->assertUnprocessable();
    }

    public function test_validates_shots_array_size_matches_shot_count(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload($user->id);
        $payload['shot_count'] = 10;

        $response = $this->actingAs($user)->postJson(
            route('practice-session.store'),
            $payload
        );

        $response->assertUnprocessable();
    }

    public function test_validates_wedge_matrix_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherMatrix = WedgeMatrix::factory()->create(['user_id' => $otherUser->id]);
        $payload = $this->validPayload($user->id, $otherMatrix->id);

        $response = $this->actingAs($user)->postJson(
            route('practice-session.store'),
            $payload
        );

        $response->assertUnprocessable();
    }

    public function test_validates_shot_fields(): void
    {
        $user = User::factory()->create();
        $payload = $this->validPayload($user->id);
        $payload['shots'][0]['actual_carry'] = -1;

        $response = $this->actingAs($user)->postJson(
            route('practice-session.store'),
            $payload
        );

        $response->assertUnprocessable();
    }

    public function test_responds_with_bad_request_when_catching_a_creation_error(): void
    {
        $user = User::factory()->create();
        $matrix = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $this->mock(PracticeSessionCreationService::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new CouldNotCreatePracticeSessionException);
        });

        $response = $this->actingAs($user)->postJson(
            route('practice-session.store'),
            $this->validPayload($user->id, $matrix->id)
        );

        $response->assertBadRequest();
        $response->assertJsonPath('message', 'Could not create practice session');
    }

    public function test_responds_with_server_error_when_catching_a_generic_exception(): void
    {
        $user = User::factory()->create();
        $matrix = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $this->mock(PracticeSessionCreationService::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new Exception);
        });

        $response = $this->actingAs($user)->postJson(
            route('practice-session.store'),
            $this->validPayload($user->id, $matrix->id)
        );

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected server error while creating practice session');
    }
}
