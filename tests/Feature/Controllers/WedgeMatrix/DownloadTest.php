<?php

namespace Feature\Controllers\WedgeMatrix;

use App\Exceptions\CouldNotDownloadWedgeMatrixException;
use App\Models\User;
use App\Services\WedgeMatrix\WedgeMatrixDownloadService;
use Exception;
use Facades\App\Models\WedgeMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_pdf_for_valid_request(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create([
            'user_id' => $user->id,
            'column_headers' => ['25%', '50%', '75%', '100%'],
            'club_labels' => ['LW', 'SW', 'GW', 'PW'],
            'yardage_values' => [
                [
                    ['carry_value' => 20, 'total_value' => 25],
                    ['carry_value' => 40, 'total_value' => 50],
                    ['carry_value' => 60, 'total_value' => 75],
                    ['carry_value' => 80, 'total_value' => 100],
                ],
            ],
        ]);

        $response = $this->actingAs($user)->getJson(
            route('wedge-matrix.download', $wedgeMatrix)
        );

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition', 'attachment; filename=wedge-matrix.pdf');
    }

    public function test_returns_403_when_user_does_not_own_matrix(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->getJson(
            route('wedge-matrix.download', $wedgeMatrix)
        );

        $response->assertForbidden();
    }

    public function test_returns_401_when_unauthenticated(): void
    {
        $wedgeMatrix = WedgeMatrix::factory()->create();

        $response = $this->getJson(
            route('wedge-matrix.download', $wedgeMatrix)
        );

        $response->assertUnauthorized();
    }

    public function test_returns_404_for_nonexistent_matrix(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(
            route('wedge-matrix.download', ['wedgeMatrix' => 99999])
        );

        $response->assertNotFound();
    }

    public function test_returns_400_when_service_throws_download_exception(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->mock(WedgeMatrixDownloadService::class, function ($mock) {
            $mock->shouldReceive('generatePdf')->andThrow(new CouldNotDownloadWedgeMatrixException);
        });

        $response = $this->actingAs($user)->getJson(
            route('wedge-matrix.download', $wedgeMatrix)
        );

        $response->assertBadRequest();
        $response->assertJsonPath('message', 'Could not download wedge matrix');
    }

    public function test_returns_500_when_service_throws_unexpected_exception(): void
    {
        $user = User::factory()->create();
        $wedgeMatrix = WedgeMatrix::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->mock(WedgeMatrixDownloadService::class, function ($mock) {
            $mock->shouldReceive('generatePdf')->andThrow(new Exception);
        });

        $response = $this->actingAs($user)->getJson(
            route('wedge-matrix.download', $wedgeMatrix)
        );

        $response->assertServerError();
        $response->assertJsonPath('message', 'Unexpected server error while downloading wedge matrix');
    }
}
