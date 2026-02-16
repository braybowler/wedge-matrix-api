<?php

namespace Feature\Controllers\WedgeMatrix;

use App\Models\User;
use App\Repositories\WedgeMatrix\WedgeMatrixRepository;
use Exception;
use Facades\App\Models\WedgeMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_responds_with_a_json_payload_on_successful_index_request(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(
            ['user_id' => $user->id]
        );

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('wedge_matrices', 1);

        $response = $this->actingAs($user)->getJson(
            route('wedge-matrix.index')
        );

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $wedgeMatrix->id);
        $response->assertJsonPath('data.0.label', $wedgeMatrix->label);
        $response->assertJsonPath('data.0.number_of_rows', $wedgeMatrix->number_of_rows);
        $response->assertJsonPath('data.0.number_of_columns', $wedgeMatrix->number_of_columns);
        $response->assertJsonPath('data.0.column_headers', $wedgeMatrix->column_headers);
        $response->assertJsonPath('data.0.values', $wedgeMatrix->values);
        $response->assertJsonPath('data.0.user_id', $wedgeMatrix->user_id);
    }

    public function test_only_responds_with_records_related_to_the_requesting_user_for_index_requests(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(
            ['user_id' => $user->id]
        );
        WedgeMatrix::factory()->create();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('wedge_matrices', 2);

        $response = $this->actingAs($user)->getJson(
            route('wedge-matrix.index')
        );

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $wedgeMatrix->id);
        $response->assertJsonPath('data.0.label', $wedgeMatrix->label);
        $response->assertJsonPath('data.0.number_of_rows', $wedgeMatrix->number_of_rows);
        $response->assertJsonPath('data.0.number_of_columns', $wedgeMatrix->number_of_columns);
        $response->assertJsonPath('data.0.column_headers', $wedgeMatrix->column_headers);
        $response->assertJsonPath('data.0.values', $wedgeMatrix->values);
        $response->assertJsonPath('data.0.user_id', $wedgeMatrix->user_id);
    }

    public function test_disallows_guest_access_for_index_requests(): void
    {
        $response = $this->getJson(
            route('wedge-matrix.index')
        );

        $response->assertUnauthorized();
    }

    public function test_responds_with_a_json_payload_when_catching_a_server_error_during_index_requests(): void
    {
        $user = User::factory()->create();
        WedgeMatrix::factory()->create(
            ['user_id' => $user->id]
        );

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('wedge_matrices', 1);

        $this->mock(WedgeMatrixRepository::class, function ($mock) {
            $mock->shouldReceive('index')
                ->once()
                ->andThrow(new Exception);
        });

        $response = $this->actingAs($user)->getJson(
            route('wedge-matrix.index')
        );

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected error while fetching wedge matrices');
    }

    public function test_logs_error_messaging_on_unsuccessful_index_requests(): void
    {
        $user = User::factory()->create();
        WedgeMatrix::factory()->create(
            ['user_id' => $user->id]
        );

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('wedge_matrices', 1);

        $this->mock(WedgeMatrixRepository::class, function ($mock) {
            $mock->shouldReceive('index')
                ->once()
                ->andThrow(new Exception);
        });

        Log::shouldReceive('error')->once()->with(
            'Server error while fetching wedge matrices: (GET /api/wedge-matrix)',
            Mockery::any()
        );

        $response = $this->actingAs($user)->getJson(
            route('wedge-matrix.index')
        );

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected error while fetching wedge matrices');
    }
}
