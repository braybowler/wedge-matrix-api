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
            'label' => $this->faker->word(),
            'number_of_rows' => 4,
            'number_of_columns' => 4,
            'column_headers' => $this->faker->words(4),
            'values' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }
}
