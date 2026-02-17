<?php

namespace Feature\Controllers;

use App\Models\User;
use App\Models\WedgeMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_dismiss_tutorial(): void
    {
        $user = User::factory()->create();
        WedgeMatrix::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($user->fresh()->has_dismissed_tutorial);

        $response = $this->actingAs($user)->patchJson(route('user.update'));

        $response->assertOk();
        $response->assertJsonPath('message', 'Tutorial dismissed.');
        $this->assertTrue($user->fresh()->has_dismissed_tutorial);
    }

    public function test_unauthenticated_user_cannot_dismiss_tutorial(): void
    {
        $response = $this->patchJson(route('user.update'));

        $response->assertUnauthorized();
    }
}
