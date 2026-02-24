<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'category_id' => fake()->randomElement([1, 2, 3, 4, 5]),
            'unit_id' => fake()->randomElement([1, 2, 3]),
            'quantity' => fake()->randomNumber(2),
            'buying_price' => fake()->randomNumber(2),
            'selling_price' => fake()->randomNumber(2),
            'quantity_alert' => fake()->randomElement([5, 10, 15]),
            'tax' => fake()->randomElement([5, 10, 15, 20, 25]),
            'tax_type' => fake()->randomElement([1, 2]),
        ];
    }
}
