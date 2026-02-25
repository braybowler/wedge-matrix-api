<?php

namespace Feature\Controllers\WedgeMatrix;

use App\Exceptions\CouldNotCreateWedgeMatrixException;
use App\Exceptions\WedgeMatrixLimitReachedException;
use App\Models\User;
use App\Services\WedgeMatrix\WedgeMatrixCreationService;
use Exception;
use Facades\App\Models\WedgeMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_responds_with_created_on_successful_store_request(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(
            route('wedge-matrix.store')
        );

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'label',
                'number_of_rows',
                'number_of_columns',
                'column_headers',
                'club_labels',
                'selected_row_display_option',
                'yardage_values',
            ],
        ]);

        $this->assertDatabaseCount('wedge_matrices', 1);
        $this->assertDatabaseHas('wedge_matrices', [
            'user_id' => $user->id,
        ]);
    }

    public function test_creates_wedge_matrix_with_default_values(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(
            route('wedge-matrix.store')
        );

        $response->assertCreated();
        $response->assertJsonPath('data.column_headers', ['25%', '50%', '75%', '100%']);
        $response->assertJsonPath('data.club_labels', ['LW', 'SW', 'GW', 'PW']);
        $response->assertJsonPath('data.user_id', $user->id);
    }

    public function test_disallows_guest_access_for_store_requests(): void
    {
        $response = $this->postJson(
            route('wedge-matrix.store')
        );

        $response->assertUnauthorized();
    }

    public function test_responds_with_unprocessable_when_wedge_matrix_limit_reached(): void
    {
        $user = User::factory()->create();

        $this->mock(WedgeMatrixCreationService::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new WedgeMatrixLimitReachedException);
        });

        $response = $this->actingAs($user)->postJson(
            route('wedge-matrix.store')
        );

        $response->assertUnprocessable();
        $response->assertJsonPath('message', 'Wedge matrix limit reached');
    }

    public function test_responds_with_bad_request_when_catching_a_creation_error(): void
    {
        $user = User::factory()->create();

        $this->mock(WedgeMatrixCreationService::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new CouldNotCreateWedgeMatrixException);
        });

        $response = $this->actingAs($user)->postJson(
            route('wedge-matrix.store')
        );

        $response->assertBadRequest();
        $response->assertJsonPath('message', 'Could not create wedge matrix');
    }

    public function test_responds_with_server_error_when_catching_a_generic_exception(): void
    {
        $user = User::factory()->create();

        $this->mock(WedgeMatrixCreationService::class, function ($mock) {
            $mock->shouldReceive('create')->andThrow(new Exception);
        });

        $response = $this->actingAs($user)->postJson(
            route('wedge-matrix.store')
        );

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected server error while creating wedge matrix');
    }

    public function test_creating_fifth_matrix_succeeds(): void
    {
        $user = User::factory()->create();
        WedgeMatrix::factory()->count(4)->create(['user_id' => $user->id]);

        $this->assertDatabaseCount('wedge_matrices', 4);

        $response = $this->actingAs($user)->postJson(
            route('wedge-matrix.store')
        );

        $response->assertCreated();
        $this->assertDatabaseCount('wedge_matrices', 5);
    }
}
