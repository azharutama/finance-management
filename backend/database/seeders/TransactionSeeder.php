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
                'created_by' => $admin?->id,
            ]);
        }
    }
}

