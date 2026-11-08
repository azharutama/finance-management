<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class CategorySeeder extends Seeder
{
    /**
     * Menjalankan seeder untuk inisialisasi data kategori awal (Salary, Other Income, Expense, dll).
     */
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

        Cache::forget('categories:all');
    }
}
