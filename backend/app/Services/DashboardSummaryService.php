<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardSummaryService
{
    /**
     * Ringkasan dashboard untuk satu bulan (default: bulan berjalan) dengan caching 5 menit.
     *
     * - total_income  = SUM(credit) seluruh transaksi bertipe credit
     * - total_expense = SUM(debit) seluruh transaksi bertipe debit
     * - net_income    = total credit - total debit
     * Transaksi soft-deleted diabaikan.
     *
     * @return array<string, mixed>
     */
    public function summarize(?string $month = null): array
    {
        $start = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : Carbon::now()->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $monthKey = $start->format('Y-m');

        return Cache::remember("dashboard_summary_{$monthKey}", 300, function () use ($start, $end, $monthKey) {
            $row = DB::table('transactions')
                ->whereNull('deleted_at')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('
                    COALESCE(SUM(credit), 0) as total_credit,
                    COALESCE(SUM(debit), 0) as total_debit,
                    SUM(CASE WHEN credit > 0 THEN 1 ELSE 0 END) as credit_count,
                    SUM(CASE WHEN debit > 0 THEN 1 ELSE 0 END) as debit_count
                ')
                ->first();

            $totalCredit = round((float) ($row->total_credit ?? 0), 2);
            $totalDebit = round((float) ($row->total_debit ?? 0), 2);

            return [
                'month' => $monthKey,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'total_income' => $totalCredit,
                'total_expense' => $totalDebit,
                'net_income' => round($totalCredit - $totalDebit, 2),
                'credit_count' => (int) ($row->credit_count ?? 0),
                'debit_count' => (int) ($row->debit_count ?? 0),
            ];
        });
    }

    /**
     * Menghapus cache ringkasan dashboard untuk bulan tertentu saat data transaksi berubah.
     */
    public static function clearCache(?string $month = null): void
    {
        if ($month) {
            Cache::forget("dashboard_summary_{$month}");
        } else {
            Cache::forget('dashboard_summary_'.now()->format('Y-m'));
        }
    }
}

