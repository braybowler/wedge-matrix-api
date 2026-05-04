<?php

namespace Tests\Feature\Controllers\PracticeSession;

use App\Models\PracticeSession;
use App\Models\User;
use App\Repositories\PracticeSession\PracticeSessionRepository;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_responds_with_a_json_payload_on_successful_index_request(): void
    {
        $user = User::factory()->create();
        $practiceSession = PracticeSession::factory()->create(
            ['user_id' => $user->id]
        );

        $this->assertDatabaseCount('practice_sessions', 1);

        $response = $this->actingAs($user)->getJson(
            route('practice-session.index')
        );

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $practiceSession->id);
        $response->assertJsonPath('data.0.user_id', $practiceSession->user_id);
        $response->assertJsonPath('data.0.shot_count', $practiceSession->shot_count);
        $response->assertJsonPath('data.0.average_difference', $practiceSession->average_difference);
        $response->assertJsonCount($practiceSession->shot_count, 'data.0.shots');
    }

    public function test_only_responds_with_records_related_to_the_requesting_user(): void
    {
        $user = User::factory()->create();
        $practiceSession = PracticeSession::factory()->create(
            ['user_id' => $user->id]
        );
        PracticeSession::factory()->create();

        $this->assertDatabaseCount('practice_sessions', 2);

        $response = $this->actingAs($user)->getJson(
            route('practice-session.index')
        );

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $practiceSession->id);
    }

    public function test_returns_sessions_in_descending_order(): void
    {
        $user = User::factory()->create();
        $older = PracticeSession::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDay(),
        ]);
        $newer = PracticeSession::factory()->create([
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson(
            route('practice-session.index')
        );

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.id', $newer->id);
        $response->assertJsonPath('data.1.id', $older->id);
    }

    public function test_disallows_guest_access_for_index_requests(): void
    {
        $response = $this->getJson(
            route('practice-session.index')
        );

        $response->assertUnauthorized();
    }

    public function test_responds_with_a_json_payload_when_catching_a_server_error_during_index_requests(): void
    {
        $user = User::factory()->create();

        $this->mock(PracticeSessionRepository::class, function ($mock) {
            $mock->shouldReceive('index')
                ->once()
                ->andThrow(new Exception);
        });

        $response = $this->actingAs($user)->getJson(
            route('practice-session.index')
        );

        $response->assertServerError();
        $response->assertJsonPath('message', 'Internal server error');
    }

}
