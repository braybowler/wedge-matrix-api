<?php

namespace Feature\Controllers\WedgeMatrix;

use App\Exceptions\CouldNotUpdateWedgeMatrixException;
use App\Models\User;
use App\Services\WedgeMatrix\WedgeMatrixUpdateService;
use Exception;
use Facades\App\Models\WedgeMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_responds_with_no_content_on_successful_update_request(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(
            ['user_id' => $user->id]
        );

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('wedge_matrices', 1);
        $this->assertDatabaseHas(
            'wedge_matrices',
            [
                'id' => $wedgeMatrix->id,
                'user_id' => $user->id,
                'number_of_columns' => 4
            ]
        );

        $response = $this->actingAs($user)->putJson(
            route('wedge-matrix.update', $wedgeMatrix),
            [
                'number_of_columns' => 3
            ]
        );

        $response->assertNoContent();
        $this->assertDatabaseHas(
            'wedge_matrices',
            [
                'id' => $wedgeMatrix->id,
                'user_id' => $user->id,
                'number_of_columns' => 3
            ]
        );

    }

    public function test_disallows_guest_access_for_update_requests(): void
    {
        $wedgeMatrix = WedgeMatrix::factory()->create();

        $response = $this->putJson(
            route('wedge-matrix.update', $wedgeMatrix)
        );

        $response->assertUnauthorized();
    }

    public function test_disallows_access_to_other_resources(): void
    {
        $user = User::factory()->create();
        $userTwo = User::factory()->create();
        WedgeMatrix::factory()->create(
            [
                'id' => 1,
                'user_id' => $user->id
            ]
        );

        $targetUnownedWedgeMatrix = WedgeMatrix::factory()->create(
            [
                'id' => 2,
                'user_id' => $userTwo->id
            ]
        );

        $response = $this->actingAs($user)->putJson(
            route('wedge-matrix.update', $targetUnownedWedgeMatrix),
            [
                'number_of_columns' => 3
            ]
        );

        $response->assertForbidden();
    }

    public static function requestProvider(): array
    {
        return [
            'number of columns' => [],
            'column headers' => [],
            'selected row display options' => [],
            'yardages values' => [],
        ];
    }

    #[DataProvider('requestProvider')]
    public function test_responds_with_a_json_payload_when_update_request_fails_validation(): void
    {
        $this->markTestSkipped();
    }

    public function test_responds_with_a_json_payload_when_catching_a_wedge_matrix_update_error_during_update_requests(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(
            ['user_id' => $user->id]
        );

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('wedge_matrices', 1);
        $this->assertDatabaseHas(
            'wedge_matrices',
            [
                'id' => $wedgeMatrix->id,
                'user_id' => $user->id,
                'number_of_columns' => 4
            ]
        );

        $this->mock(WedgeMatrixUpdateService::class, function ($mock) {
            $mock->shouldReceive('update')->andThrow(new CouldNotUpdateWedgeMatrixException());
        });

        $response = $this->actingAs($user)->putJson(
            route('wedge-matrix.update', $wedgeMatrix),
            [
                'number_of_columns' => 3
            ]
        );

        $response->assertBadRequest();
        $response->assertJsonPath('message', 'Could not update wedge matrix');
    }

    public function test_responds_with_a_json_payload_when_catching_a_server_error_during_update_requests(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(
            ['user_id' => $user->id]
        );

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('wedge_matrices', 1);
        $this->assertDatabaseHas(
            'wedge_matrices',
            [
                'id' => $wedgeMatrix->id,
                'user_id' => $user->id,
                'number_of_columns' => 4
            ]
        );

        $this->mock(WedgeMatrixUpdateService::class, function ($mock) {
            $mock->shouldReceive('update')->andThrow(new Exception());
        });

        $response = $this->actingAs($user)->putJson(
            route('wedge-matrix.update', $wedgeMatrix),
            [
                'number_of_columns' => 3
            ]
        );

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected server error while updating wedge matrix');
    }
}
