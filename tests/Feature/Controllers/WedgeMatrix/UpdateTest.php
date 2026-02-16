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
                'number_of_columns' => 4,
            ]
        );

        $response = $this->actingAs($user)->putJson(
            route('wedge-matrix.update', $wedgeMatrix),
            [
                'number_of_columns' => 3,
            ]
        );

        $response->assertNoContent();
        $this->assertDatabaseHas(
            'wedge_matrices',
            [
                'id' => $wedgeMatrix->id,
                'user_id' => $user->id,
                'number_of_columns' => 3,
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
                'user_id' => $user->id,
            ]
        );

        $targetUnownedWedgeMatrix = WedgeMatrix::factory()->create(
            [
                'id' => 2,
                'user_id' => $userTwo->id,
            ]
        );

        $response = $this->actingAs($user)->putJson(
            route('wedge-matrix.update', $targetUnownedWedgeMatrix),
            [
                'number_of_columns' => 3,
            ]
        );

        $response->assertForbidden();
    }

    public static function requestProvider(): array
    {
        return [
            'number_of_columns must be an integer' => [
                ['number_of_columns' => 'not-an-integer'],
            ],
            'number_of_columns must be at least 1' => [
                ['number_of_columns' => 0],
            ],
            'number_of_columns must be at most 4' => [
                ['number_of_columns' => 5],
            ],
            'column_headers must be an array' => [
                ['column_headers' => 'not-an-array'],
            ],
            'column_headers items must be strings' => [
                ['column_headers' => [123]],
            ],
            'selected_row_display_option must be a valid option' => [
                ['selected_row_display_option' => 'Invalid'],
            ],
            'yardage_values must be an array' => [
                ['yardage_values' => 'not-an-array'],
            ],
            'yardage_values items must be arrays' => [
                ['yardage_values' => ['not-an-array']],
            ],
            'yardage_values carry_value must be numeric' => [
                ['yardage_values' => [[['carry_value' => 'abc', 'total_value' => 100]]]],
            ],
            'yardage_values total_value must be numeric' => [
                ['yardage_values' => [[['carry_value' => 100, 'total_value' => 'abc']]]],
            ],
        ];
    }

    #[DataProvider('requestProvider')]
    public function test_responds_with_a_json_payload_when_update_request_fails_validation(array $payload): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson(
            route('wedge-matrix.update', $wedgeMatrix),
            $payload,
        );

        $response->assertUnprocessable();
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
                'number_of_columns' => 4,
            ]
        );

        $this->mock(WedgeMatrixUpdateService::class, function ($mock) {
            $mock->shouldReceive('update')->andThrow(new CouldNotUpdateWedgeMatrixException);
        });

        $response = $this->actingAs($user)->putJson(
            route('wedge-matrix.update', $wedgeMatrix),
            [
                'number_of_columns' => 3,
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
                'number_of_columns' => 4,
            ]
        );

        $this->mock(WedgeMatrixUpdateService::class, function ($mock) {
            $mock->shouldReceive('update')->andThrow(new Exception);
        });

        $response = $this->actingAs($user)->putJson(
            route('wedge-matrix.update', $wedgeMatrix),
            [
                'number_of_columns' => 3,
            ]
        );

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected server error while updating wedge matrix');
    }
}
