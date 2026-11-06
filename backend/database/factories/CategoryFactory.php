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
            'name' => fake()->unique()->words(2, true).' '.fake()->numberBetween(100, 999),
            'type' => fake()->randomElement([Category::TYPE_INCOME, Category::TYPE_EXPENSE]),
            'is_active' => true,
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => [
            'type' => Category::TYPE_INCOME,
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn () => [
            'type' => Category::TYPE_EXPENSE,
        ]);
    }
}
