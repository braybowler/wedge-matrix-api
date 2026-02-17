<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WedgeMatrix;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class WedgeMatrixFactory extends Factory
{
    protected $model = WedgeMatrix::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => $this->faker->word(),
            'number_of_rows' => 4,
            'number_of_columns' => 4,
            'column_headers' => $this->faker->words(4),
            'club_labels' => ['LW', 'SW', 'GW', 'PW'],
            'selected_row_display_option' => 'Both',
            'yardage_values' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
