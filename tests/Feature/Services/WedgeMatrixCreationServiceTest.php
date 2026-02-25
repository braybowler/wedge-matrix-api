<?php

namespace Feature\Services;

use App\Exceptions\CouldNotCreateWedgeMatrixException;
use App\Exceptions\WedgeMatrixLimitReachedException;
use App\Models\User;
use App\Models\WedgeMatrix;
use App\Services\WedgeMatrix\WedgeMatrixCreationService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class WedgeMatrixCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_wedge_matrix_with_default_values(): void
    {
        $user = User::factory()->create();

        $service = app(WedgeMatrixCreationService::class);
        $wedgeMatrix = $service->create($user);

        $this->assertInstanceOf(WedgeMatrix::class, $wedgeMatrix);
        $this->assertEquals($user->id, $wedgeMatrix->user_id);
        $this->assertEquals(['25%', '50%', '75%', '100%'], $wedgeMatrix->column_headers);
        $this->assertEquals(['LW', 'SW', 'GW', 'PW'], $wedgeMatrix->club_labels);
        $this->assertDatabaseCount('wedge_matrices', 1);
    }

    public function test_throws_wedge_matrix_limit_reached_exception_when_user_has_five_matrices(): void
    {
        $user = User::factory()->create();
        WedgeMatrix::factory()->count(5)->create(['user_id' => $user->id]);

        $this->expectException(WedgeMatrixLimitReachedException::class);

        $service = app(WedgeMatrixCreationService::class);
        $service->create($user);
    }

    public function test_allows_creating_up_to_five_matrices(): void
    {
        $user = User::factory()->create();
        WedgeMatrix::factory()->count(4)->create(['user_id' => $user->id]);

        $service = app(WedgeMatrixCreationService::class);
        $wedgeMatrix = $service->create($user);

        $this->assertInstanceOf(WedgeMatrix::class, $wedgeMatrix);
        $this->assertDatabaseCount('wedge_matrices', 5);
    }

    public function test_throws_could_not_create_wedge_matrix_exception_on_query_exception(): void
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('count')->andReturn(0);
        $hasMany->shouldReceive('create')
            ->once()
            ->andThrow(new QueryException('test', '', [], new Exception));

        $user = Mockery::mock(User::class);
        $user->shouldReceive('wedgeMatrices')->andReturn($hasMany);

        Log::shouldReceive('error')->once()->with(
            'Failed to create wedge matrix',
            Mockery::any()
        );

        $this->expectException(CouldNotCreateWedgeMatrixException::class);

        $service = app(WedgeMatrixCreationService::class);
        $service->create($user);
    }

    public function test_rethrows_generic_exceptions(): void
    {
        $hasMany = Mockery::mock(HasMany::class);
        $hasMany->shouldReceive('count')->andReturn(0);
        $hasMany->shouldReceive('create')
            ->once()
            ->andThrow(new Exception('Something went wrong'));

        $user = Mockery::mock(User::class);
        $user->shouldReceive('wedgeMatrices')->andReturn($hasMany);

        Log::shouldReceive('error')->once()->with(
            'Server error while creating wedge matrix',
            Mockery::any()
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong');

        $service = app(WedgeMatrixCreationService::class);
        $service->create($user);
    }
}
