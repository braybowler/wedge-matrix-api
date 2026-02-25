<?php

namespace Feature\Controllers\WedgeMatrix;

use App\Exceptions\CannotDeleteLastWedgeMatrixException;
use App\Exceptions\CouldNotDeleteWedgeMatrixException;
use App\Models\User;
use App\Services\WedgeMatrix\WedgeMatrixDeletionService;
use Exception;
use Facades\App\Models\WedgeMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_responds_with_no_content_on_successful_delete_request(): void
    {
        $user = User::factory()->create();
        $wedgeMatrixOne = WedgeMatrix::factory()->create(['user_id' => $user->id]);
        $wedgeMatrixTwo = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseCount('wedge_matrices', 2);

        $response = $this->actingAs($user)->deleteJson(
            route('wedge-matrix.destroy', $wedgeMatrixOne)
        );

        $response->assertNoContent();
        $this->assertDatabaseCount('wedge_matrices', 1);
        $this->assertDatabaseMissing('wedge_matrices', ['id' => $wedgeMatrixOne->id]);
        $this->assertDatabaseHas('wedge_matrices', ['id' => $wedgeMatrixTwo->id]);
    }

    public function test_disallows_guest_access_for_delete_requests(): void
    {
        $wedgeMatrix = WedgeMatrix::factory()->create();

        $response = $this->deleteJson(
            route('wedge-matrix.destroy', $wedgeMatrix)
        );

        $response->assertUnauthorized();
    }

    public function test_disallows_access_to_other_resources(): void
    {
        $user = User::factory()->create();
        $userTwo = User::factory()->create();
        WedgeMatrix::factory()->create([
            'id' => 1,
            'user_id' => $user->id,
        ]);

        $targetUnownedWedgeMatrix = WedgeMatrix::factory()->create([
            'id' => 2,
            'user_id' => $userTwo->id,
        ]);

        $response = $this->actingAs($user)->deleteJson(
            route('wedge-matrix.destroy', $targetUnownedWedgeMatrix)
        );

        $response->assertForbidden();
    }

    public function test_responds_with_unprocessable_when_deleting_last_matrix(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $this->mock(WedgeMatrixDeletionService::class, function ($mock) {
            $mock->shouldReceive('delete')->andThrow(new CannotDeleteLastWedgeMatrixException);
        });

        $response = $this->actingAs($user)->deleteJson(
            route('wedge-matrix.destroy', $wedgeMatrix)
        );

        $response->assertUnprocessable();
        $response->assertJsonPath('message', 'Cannot delete the last wedge matrix');
    }

    public function test_responds_with_bad_request_when_catching_a_deletion_error(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $this->mock(WedgeMatrixDeletionService::class, function ($mock) {
            $mock->shouldReceive('delete')->andThrow(new CouldNotDeleteWedgeMatrixException);
        });

        $response = $this->actingAs($user)->deleteJson(
            route('wedge-matrix.destroy', $wedgeMatrix)
        );

        $response->assertBadRequest();
        $response->assertJsonPath('message', 'Could not delete wedge matrix');
    }

    public function test_responds_with_server_error_when_catching_a_generic_exception(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $this->mock(WedgeMatrixDeletionService::class, function ($mock) {
            $mock->shouldReceive('delete')->andThrow(new Exception);
        });

        $response = $this->actingAs($user)->deleteJson(
            route('wedge-matrix.destroy', $wedgeMatrix)
        );

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected server error while deleting wedge matrix');
    }
}
