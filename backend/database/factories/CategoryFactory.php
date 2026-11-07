<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true).' Expense '.fake()->numberBetween(100, 999),
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => [
            'name' => fake()->unique()->word().' Income '.fake()->numberBetween(100, 999),
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn () => [
            'name' => fake()->unique()->word().' Expense '.fake()->numberBetween(100, 999),
        ]);
    }
}
