<?php

namespace Feature\Repositories\WedgeMatrix;

use App\Models\User;
use App\Models\WedgeMatrix;
use App\Repositories\WedgeMatrix\WedgeMatrixRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class WedgeMatrixRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_method_fetches_all_wedge_matrices_for_the_active_user(): void
    {
        $user = User::factory()->create();
        WedgeMatrix::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        Auth::setUser($user);

        $repository = new WedgeMatrixRepository();

        $wedgeMatrices = $repository->index()->get();

        $this->assertEquals(3, $wedgeMatrices->count());
    }

    public function test_index_method_fetches_will_not_return_other_users_resources(): void
    {
        $user = User::factory()->create();
        WedgeMatrix::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $otherUser = User::factory()->create();
        WedgeMatrix::factory()->count(3)->create([
            'user_id' => $otherUser->id,
        ]);

        Auth::setUser($user);

        $repository = new WedgeMatrixRepository();

        $wedgeMatrices = $repository->index()->get();

        $this->assertEquals(3, $wedgeMatrices->count());
    }

}

