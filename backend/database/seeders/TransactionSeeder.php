<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        if (Transaction::count() > 0) {
            return;
        }

        $admin = User::where('email', 'admin@example.com')->first();
        $coas = ChartOfAccount::pluck('id', 'code');

        // Seed data for 2022 (matching the exact spreadsheet specification)
        $sampleTransactions = [
            // 2022-01 (Salary: 12,000,000 | Other Income: 5,500,000 | Family: 500,000 | Transport: 200,000 | Meal: 150,000)
            ['date' => '2022-01-01', 'coa' => '401', 'description' => 'Gaji Di Perusahaan A', 'debit' => 0, 'credit' => 5000000],
            ['date' => '2022-01-02', 'coa' => '402', 'description' => 'Gaji Ketum', 'debit' => 0, 'credit' => 7000000],
            ['date' => '2022-01-05', 'coa' => '403', 'description' => 'Profit Trading Januari', 'debit' => 0, 'credit' => 5500000],
            ['date' => '2022-01-10', 'coa' => '602', 'description' => 'Bensin Anak', 'debit' => 25000, 'credit' => 0],
            ['date' => '2022-01-12', 'coa' => '603', 'description' => 'Parkir & Tol Bulanan', 'debit' => 175000, 'credit' => 0],
            ['date' => '2022-01-15', 'coa' => '601', 'description' => 'Biaya Sekolah Anak', 'debit' => 500000, 'credit' => 0],
            ['date' => '2022-01-20', 'coa' => '604', 'description' => 'Makan Siang', 'debit' => 150000, 'credit' => 0],

            // 2022-02 (Salary: 12,000,000 | Other Income: 6,000,000 | Family: 3,500,000 | Transport: 250,000 | Meal: 300,000)
            ['date' => '2022-02-01', 'coa' => '401', 'description' => 'Gaji Di Perusahaan A', 'debit' => 0, 'credit' => 5000000],
            ['date' => '2022-02-02', 'coa' => '402', 'description' => 'Gaji Ketum', 'debit' => 0, 'credit' => 7000000],
            ['date' => '2022-02-05', 'coa' => '403', 'description' => 'Profit Trading Februari', 'debit' => 0, 'credit' => 6000000],
            ['date' => '2022-02-10', 'coa' => '601', 'description' => 'Biaya Sekolah & Les', 'debit' => 3500000, 'credit' => 0],
            ['date' => '2022-02-14', 'coa' => '602', 'description' => 'Bensin Operasional', 'debit' => 250000, 'credit' => 0],
            ['date' => '2022-02-18', 'coa' => '605', 'description' => 'Makanan Pokok Bulanan', 'debit' => 300000, 'credit' => 0],

            // 2022-03 (Salary: 12,000,000 | Other Income: 3,500,000 | Family: 4,500,000 | Transport: 225,000 | Meal: 175,000)
            ['date' => '2022-03-01', 'coa' => '401', 'description' => 'Gaji Di Perusahaan A', 'debit' => 0, 'credit' => 5000000],
            ['date' => '2022-03-02', 'coa' => '402', 'description' => 'Gaji Ketum', 'debit' => 0, 'credit' => 7000000],
            ['date' => '2022-03-06', 'coa' => '403', 'description' => 'Profit Trading Maret', 'debit' => 0, 'credit' => 3500000],
            ['date' => '2022-03-11', 'coa' => '601', 'description' => 'Biaya Sekolah Semester', 'debit' => 4500000, 'credit' => 0],
            ['date' => '2022-03-15', 'coa' => '602', 'description' => 'Bensin & Parkir', 'debit' => 225000, 'credit' => 0],
            ['date' => '2022-03-22', 'coa' => '604', 'description' => 'Makan Siang & Pokok', 'debit' => 175000, 'credit' => 0],
        ];

        foreach ($sampleTransactions as $tx) {
            $coaId = $coas[$tx['coa']] ?? null;
            if (! $coaId) {
                continue;
            }

            Transaction::create([
                'date' => $tx['date'],
                'coa_id' => $coaId,
                'description' => $tx['description'],
                'debit' => $tx['debit'],
                'credit' => $tx['credit'],
            ]);
        }
    }
}
