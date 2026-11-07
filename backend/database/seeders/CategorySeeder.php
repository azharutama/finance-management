<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categoriesData = [
            ['name' => 'Salary'],
            ['name' => 'Other Income'],
            ['name' => 'Family Expense'],
            ['name' => 'Transport Expense'],
            ['name' => 'Meal Expense'],
        ];

        foreach ($categoriesData as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name']]
            );
        }
    }
}
