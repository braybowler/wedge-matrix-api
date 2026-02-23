<?php

namespace Feature\Controllers;

use App\Models\User;
use App\Models\WedgeMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_their_profile(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson(route('user.show'));

        $response->assertOk();
        $response->assertJsonPath('data.id', $user->id);
        $response->assertJsonPath('data.email', $user->email);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'email',
                'has_dismissed_tutorial',
                'wedge_matrices',
            ],
        ]);
        $response->assertJsonPath('data.wedge_matrices.0.id', $wedgeMatrix->id);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson(route('user.show'));

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_dismiss_tutorial(): void
    {
        $user = User::factory()->create();
        WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($user->fresh()->has_dismissed_tutorial);

        $response = $this->actingAs($user)->patchJson(route('user.update'), [
            'has_dismissed_tutorial' => true,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.has_dismissed_tutorial', true);
        $this->assertTrue($user->fresh()->has_dismissed_tutorial);
    }

    public function test_validation_fails_without_has_dismissed_tutorial(): void
    {
        $user = User::factory()->create();
        WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->patchJson(route('user.update'));

        $response->assertUnprocessable();
    }

    public function test_unauthenticated_user_cannot_dismiss_tutorial(): void
    {
        $response = $this->patchJson(route('user.update'));

        $response->assertUnauthorized();
    }
}
