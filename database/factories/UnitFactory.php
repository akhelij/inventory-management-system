<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fake()->word(),
            'name' => fake()->words(2, true),
        ];
    }
}
