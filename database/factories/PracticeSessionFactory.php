<?php

namespace Database\Factories;

use App\Models\PracticeSession;
use App\Models\User;
use App\Models\WedgeMatrix;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PracticeSessionFactory extends Factory
{
    protected $model = PracticeSession::class;

    public function definition(): array
    {
        $shotCount = $this->faker->randomElement([5, 10, 15]);
        $shots = [];
        $totalDifference = 0;

        for ($i = 1; $i <= $shotCount; $i++) {
            $target = $this->faker->numberBetween(5, 120);
            $actual = $this->faker->randomFloat(1, 0, 150);
            $difference = round(abs($target - $actual), 1);
            $totalDifference += $difference;

            $shots[] = [
                'shot_number' => $i,
                'target_yards' => $target,
                'actual_carry' => $actual,
                'difference' => $difference,
            ];
        }

        return [
            'user_id' => User::factory(),
            'wedge_matrix_id' => WedgeMatrix::factory(),
            'mode' => 'gauntlet',
            'shot_count' => $shotCount,
            'shots' => $shots,
            'average_difference' => round($totalDifference / $shotCount, 1),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
