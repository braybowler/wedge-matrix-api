<?php

namespace Feature\Services;

use App\Exceptions\CouldNotUpdateWedgeMatrixException;
use App\Models\User;
use App\Models\WedgeMatrix;
use App\Services\WedgeMatrix\WedgeMatrixUpdateService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class WedgeMatrixUpdateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_a_wedge_matrix(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create([
            'user_id' => $user->id,
            'number_of_columns' => 4,
        ]);

        $service = app(WedgeMatrixUpdateService::class);
        $service->update($wedgeMatrix, ['number_of_columns' => 3]);

        $this->assertDatabaseHas('wedge_matrices', [
            'id' => $wedgeMatrix->id,
            'number_of_columns' => 3,
        ]);
    }

    public function test_throws_could_not_update_wedge_matrix_exception_on_query_exception(): void
    {
        $wedgeMatrix = Mockery::mock(WedgeMatrix::class);
        $wedgeMatrix->shouldReceive('update')
            ->once()
            ->andThrow(new QueryException('test', '', [], new Exception));

        Log::shouldReceive('error')->once()->with(
            'Failed to update wedge matrix',
            Mockery::any()
        );

        $this->expectException(CouldNotUpdateWedgeMatrixException::class);

        $service = app(WedgeMatrixUpdateService::class);
        $service->update($wedgeMatrix, ['number_of_columns' => 3]);
    }

    public function test_rethrows_generic_exceptions(): void
    {
        $wedgeMatrix = Mockery::mock(WedgeMatrix::class);
        $wedgeMatrix->shouldReceive('update')
            ->once()
            ->andThrow(new Exception('Something went wrong'));

        Log::shouldReceive('error')->once()->with(
            'Server error while updating wedge matrix',
            Mockery::any()
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong');

        $service = app(WedgeMatrixUpdateService::class);
        $service->update($wedgeMatrix, ['number_of_columns' => 3]);
    }
}
