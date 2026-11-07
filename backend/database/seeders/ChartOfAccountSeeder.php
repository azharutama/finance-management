<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Menjalankan seeder untuk inisialisasi akun-akun COA (401 - 605) sesuai acuan spreadsheet.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        $coaData = [
            ['code' => '401', 'name' => 'Gaji Karyawan', 'category' => 'Salary'],
            ['code' => '402', 'name' => 'Gaji Ketua MPR', 'category' => 'Salary'],
            ['code' => '403', 'name' => 'Profit Trading', 'category' => 'Other Income'],
            ['code' => '601', 'name' => 'Biaya Sekolah', 'category' => 'Family Expense'],
            ['code' => '602', 'name' => 'Bensin', 'category' => 'Transport Expense'],
            ['code' => '603', 'name' => 'Parkir', 'category' => 'Transport Expense'],
            ['code' => '604', 'name' => 'Makan Siang', 'category' => 'Meal Expense'],
            ['code' => '605', 'name' => 'Makanan Pokok Bulanan', 'category' => 'Meal Expense'],
        ];

        foreach ($coaData as $coa) {
            $categoryId = $categories[$coa['category']] ?? null;
            if (! $categoryId) {
                continue;
            }

            ChartOfAccount::updateOrCreate(
                ['code' => $coa['code']],
                [
                    'name' => $coa['name'],
                    'category_id' => $categoryId,
                ]
            );
        }
    }
}
