<?php

namespace Feature\Services;

use App\Exceptions\CannotDeleteLastWedgeMatrixException;
use App\Exceptions\CouldNotDeleteWedgeMatrixException;
use App\Models\User;
use App\Models\WedgeMatrix;
use App\Services\WedgeMatrix\WedgeMatrixDeletionService;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class WedgeMatrixDeletionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_a_wedge_matrix(): void
    {
        $user = User::factory()->create();
        $wedgeMatrixOne = WedgeMatrix::factory()->create(['user_id' => $user->id]);
        $wedgeMatrixTwo = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseCount('wedge_matrices', 2);

        $service = app(WedgeMatrixDeletionService::class);
        $service->delete($wedgeMatrixOne);

        $this->assertDatabaseCount('wedge_matrices', 1);
        $this->assertDatabaseMissing('wedge_matrices', ['id' => $wedgeMatrixOne->id]);
    }

    public function test_throws_cannot_delete_last_wedge_matrix_exception(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $this->expectException(CannotDeleteLastWedgeMatrixException::class);

        $service = app(WedgeMatrixDeletionService::class);
        $service->delete($wedgeMatrix);
    }

    public function test_throws_could_not_delete_wedge_matrix_exception_on_query_exception(): void
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('count')->andReturn(2);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('wedgeMatrices')->andReturn($hasMany);

        $wedgeMatrix = Mockery::mock(WedgeMatrix::class);
        $wedgeMatrix->shouldReceive('getAttribute')->with('user')->andReturn($user);
        $wedgeMatrix->shouldReceive('delete')
            ->once()
            ->andThrow(new QueryException('test', '', [], new Exception));

        Log::shouldReceive('error')->once()->with(
            'Failed to delete wedge matrix',
            Mockery::any()
        );

        $this->expectException(CouldNotDeleteWedgeMatrixException::class);

        $service = app(WedgeMatrixDeletionService::class);
        $service->delete($wedgeMatrix);
    }

    public function test_rethrows_generic_exceptions(): void
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('count')->andReturn(2);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('wedgeMatrices')->andReturn($hasMany);

        $wedgeMatrix = Mockery::mock(WedgeMatrix::class);
        $wedgeMatrix->shouldReceive('getAttribute')->with('user')->andReturn($user);
        $wedgeMatrix->shouldReceive('delete')
            ->once()
            ->andThrow(new Exception('Something went wrong'));

        Log::shouldReceive('error')->once()->with(
            'Server error while deleting wedge matrix',
            Mockery::any()
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong');

        $service = app(WedgeMatrixDeletionService::class);
        $service->delete($wedgeMatrix);
    }
}
