<?php

namespace App\Services;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitAndLossService
{
    /**
     * Generate the monthly Profit & Loss report using SQL aggregation.
     *
     * @param  array{start_month?: ?string, end_month?: ?string, start_date?: ?string, end_date?: ?string}  $filters
     * @return array<string, mixed>
     */
    public function generateReport(array $filters = []): array
    {
        [$startDate, $endDate] = $this->resolvePeriod($filters);

        $months = $this->buildMonthsList($startDate, $endDate);

        $categories = Category::query()
            ->select(['id', 'name', 'type', 'is_active'])
            ->orderByRaw("CASE WHEN type = 'income' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get();

        $driver = DB::getDriverName();
        $monthExpression = match ($driver) {
            'sqlite' => "strftime('%Y-%m', transactions.date)",
            'pgsql' => "to_char(transactions.date, 'YYYY-MM')",
            default => "DATE_FORMAT(transactions.date, '%Y-%m')",
        };

        $aggregatedRows = DB::table('transactions')
            ->join('chart_of_accounts', 'transactions.coa_id', '=', 'chart_of_accounts.id')
            ->join('categories', 'chart_of_accounts.category_id', '=', 'categories.id')
            ->whereBetween('transactions.date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw("
                categories.id as category_id,
                {$monthExpression} as period_month,
                COALESCE(SUM(transactions.debit), 0) as total_debit,
                COALESCE(SUM(transactions.credit), 0) as total_credit
            ")
            ->groupBy('categories.id', DB::raw($monthExpression))
            ->get();

        $lookup = [];
        foreach ($aggregatedRows as $row) {
            $catId = (int) $row->category_id;
            $monthKey = (string) $row->period_month;
            $lookup[$catId][$monthKey] = [
                'debit' => round((float) $row->total_debit, 2),
                'credit' => round((float) $row->total_credit, 2),
            ];
        }

        $incomeCategories = [];
        $expenseCategories = [];

        $totalsByMonth = [];
        foreach ($months as $month) {
            $totalsByMonth[$month] = [
                'total_income' => 0.0,
                'total_expense' => 0.0,
                'net_income' => 0.0,
            ];
        }

        foreach ($categories as $category) {
            $isIncome = $category->type === Category::TYPE_INCOME;
            $monthlyValues = [];
            $categoryTotal = 0.0;

            foreach ($months as $month) {
                $cell = $lookup[$category->id][$month] ?? ['debit' => 0.0, 'credit' => 0.0];
                $amount = $isIncome ? $cell['credit'] : $cell['debit'];
                $amount = round((float) $amount, 2);

                $monthlyValues[$month] = $amount;
                $categoryTotal = round($categoryTotal + $amount, 2);

                if ($isIncome) {
                    $totalsByMonth[$month]['total_income'] = round(
                        $totalsByMonth[$month]['total_income'] + $amount,
                        2
                    );
                } else {
                    $totalsByMonth[$month]['total_expense'] = round(
                        $totalsByMonth[$month]['total_expense'] + $amount,
                        2
                    );
                }
            }

            $item = [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'type' => $category->type,
                'monthly' => $monthlyValues,
                'total' => $categoryTotal,
            ];

            if ($isIncome) {
                $incomeCategories[] = $item;
            } else {
                $expenseCategories[] = $item;
            }
        }

        $grandTotalIncome = 0.0;
        $grandTotalExpense = 0.0;

        foreach ($months as $month) {
            $income = $totalsByMonth[$month]['total_income'];
            $expense = $totalsByMonth[$month]['total_expense'];
            $totalsByMonth[$month]['net_income'] = round($income - $expense, 2);

            $grandTotalIncome = round($grandTotalIncome + $income, 2);
            $grandTotalExpense = round($grandTotalExpense + $expense, 2);
        }

        return [
            'period' => [
                'start_month' => $startDate->format('Y-m'),
                'end_month' => $endDate->format('Y-m'),
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'months' => $months,
            'income_categories' => $incomeCategories,
            'expense_categories' => $expenseCategories,
            'totals_by_month' => $totalsByMonth,
            'summary' => [
                'total_income' => $grandTotalIncome,
                'total_expense' => $grandTotalExpense,
                'net_income' => round($grandTotalIncome - $grandTotalExpense, 2),
            ],
        ];
    }

    /**
     * Resolve start and end Carbon dates from filter input.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolvePeriod(array $filters): array
    {
        if (! empty($filters['start_date']) || ! empty($filters['end_date'])) {
            $start = ! empty($filters['start_date'])
                ? Carbon::createFromFormat('Y-m-d', $filters['start_date'])->startOfDay()
                : Carbon::createFromFormat('Y-m-d', $filters['end_date'])->startOfYear();

            $end = ! empty($filters['end_date'])
                ? Carbon::createFromFormat('Y-m-d', $filters['end_date'])->endOfDay()
                : $start->copy()->endOfMonth();

            return [$start, $end];
        }

        if (empty($filters['start_month']) && empty($filters['end_month'])) {
            $minDate = DB::table('transactions')->min('date');
            $defaultYear = $minDate ? Carbon::parse($minDate)->format('Y') : date('Y');
            $startMonthStr = "{$defaultYear}-01";
            $endMonthStr = "{$defaultYear}-03";
        } else {
            $year = date('Y');
            $startMonthStr = $filters['start_month'] ?? "{$year}-01";
            $endMonthStr = $filters['end_month'] ?? ($filters['start_month'] ?? "{$year}-03");
        }

        $start = Carbon::createFromFormat('Y-m', $startMonthStr)->startOfMonth();
        $end = Carbon::createFromFormat('Y-m', $endMonthStr)->endOfMonth();

        if ($end->lt($start)) {
            $end = $start->copy()->endOfMonth();
        }

        return [$start, $end];
    }

    /**
     * Build ordered list of 'YYYY-MM' month keys between start and end dates.
     *
     * @return array<int, string>
     */
    private function buildMonthsList(Carbon $startDate, Carbon $endDate): array
    {
        $months = [];
        $cursor = $startDate->copy()->startOfMonth();
        $lastMonth = $endDate->copy()->startOfMonth();

        while ($cursor->lte($lastMonth)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        return $months;
    }
}

