<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'Staff Viewer',
                'role' => 'viewer',
                'password' => Hash::make('password'),
            ]
        );

        $categoriesData = [
            ['name' => 'Salary', 'type' => Category::TYPE_INCOME],
            ['name' => 'Other Income', 'type' => Category::TYPE_INCOME],
            ['name' => 'Family Expense', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Transport Expense', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Meal Expense', 'type' => Category::TYPE_EXPENSE],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['name']] = Category::updateOrCreate(
                ['name' => $cat['name']],
                ['type' => $cat['type'], 'is_active' => true]
            );
        }

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

        $coas = [];
        foreach ($coaData as $coa) {
            $coas[$coa['code']] = ChartOfAccount::updateOrCreate(
                ['code' => $coa['code']],
                [
                    'name' => $coa['name'],
                    'category_id' => $categories[$coa['category']]->id,
                    'is_active' => true,
                ]
            );
        }

        if (Transaction::count() === 0) {
            $year = date('Y');
            $sampleTransactions = [
                // January
                ['date' => "{$year}-01-01", 'coa' => '401', 'description' => 'Gaji Di Perusahaan A', 'debit' => 0, 'credit' => 5000000],
                ['date' => "{$year}-01-02", 'coa' => '402', 'description' => 'Gaji Ketum', 'debit' => 0, 'credit' => 7000000],
                ['date' => "{$year}-01-10", 'coa' => '602', 'description' => 'Bensin Mobil', 'debit' => 25000, 'credit' => 0],
                ['date' => "{$year}-01-12", 'coa' => '604', 'description' => 'Makan Siang Kantor', 'debit' => 45000, 'credit' => 0],
                ['date' => "{$year}-01-15", 'coa' => '601', 'description' => 'SPP Anak Januari', 'debit' => 750000, 'credit' => 0],
                // February
                ['date' => "{$year}-02-01", 'coa' => '401', 'description' => 'Gaji Di Perusahaan A', 'debit' => 0, 'credit' => 5000000],
                ['date' => "{$year}-02-02", 'coa' => '403', 'description' => 'Profit Trading Saham', 'debit' => 0, 'credit' => 1500000],
                ['date' => "{$year}-02-08", 'coa' => '603', 'description' => 'Parkir Mall', 'debit' => 15000, 'credit' => 0],
                ['date' => "{$year}-02-15", 'coa' => '605', 'description' => 'Belanja Bulanan Keluarga', 'debit' => 1200000, 'credit' => 0],
                // March
                ['date' => "{$year}-03-01", 'coa' => '401', 'description' => 'Gaji Di Perusahaan A', 'debit' => 0, 'credit' => 5000000],
                ['date' => "{$year}-03-02", 'coa' => '402', 'description' => 'Gaji Ketum', 'debit' => 0, 'credit' => 7000000],
                ['date' => "{$year}-03-10", 'coa' => '602', 'description' => 'Isi BBM Pertamax', 'debit' => 300000, 'credit' => 0],
                ['date' => "{$year}-03-18", 'coa' => '604', 'description' => 'Makan Siang Tim', 'debit' => 175000, 'credit' => 0],
            ];

            foreach ($sampleTransactions as $tx) {
                Transaction::create([
                    'date' => $tx['date'],
                    'coa_id' => $coas[$tx['coa']]->id,
                    'description' => $tx['description'],
                    'debit' => $tx['debit'],
                    'credit' => $tx['credit'],
                    'created_by' => $admin->id,
                ]);
            }
        }
    }
}
