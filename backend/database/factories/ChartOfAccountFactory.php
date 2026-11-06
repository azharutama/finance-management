<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChartOfAccount>
 */
class ChartOfAccountFactory extends Factory
{
    protected $model = ChartOfAccount::class;

    public function definition(): array
    {
        return [
            'code' => (string) fake()->unique()->numberBetween(100, 9999),
            'name' => fake()->words(3, true),
            'category_id' => Category::factory(),
            'is_active' => true,
        ];
    }
}
