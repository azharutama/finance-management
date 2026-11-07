<?php

namespace Tests\Unit;

use App\Exports\ProfitAndLossExport;
use PHPUnit\Framework\TestCase;

class ProfitAndLossExportServiceTest extends TestCase
{
    /**
     * Memastikan baris data yang dibentuk export class sesuai dengan spesifikasi spreadsheet.
     */
    public function test_it_builds_export_rows_matching_spreadsheet_structure(): void
    {
        $sampleReport = [
            'period' => [
                'start_month' => '2022-01',
                'end_month' => '2022-03',
                'start_date' => '2022-01-01',
                'end_date' => '2022-03-31',
            ],
            'months' => ['2022-01', '2022-02', '2022-03'],
            'income_categories' => [
                [
                    'category_id' => 1,
                    'category_name' => 'Salary',
                    'type' => 'income',
                    'monthly' => ['2022-01' => 12000000, '2022-02' => 12000000, '2022-03' => 12000000],
                    'total' => 36000000,
                ],
                [
                    'category_id' => 2,
                    'category_name' => 'Other Income',
                    'type' => 'income',
                    'monthly' => ['2022-01' => 5500000, '2022-02' => 6000000, '2022-03' => 3500000],
                    'total' => 15000000,
                ],
            ],
            'expense_categories' => [
                [
                    'category_id' => 3,
                    'category_name' => 'Family Expense',
                    'type' => 'expense',
                    'monthly' => ['2022-01' => 500000, '2022-02' => 3500000, '2022-03' => 4500000],
                    'total' => 8500000,
                ],
            ],
            'totals_by_month' => [
                '2022-01' => [
                    'total_income' => 17500000,
                    'total_expense' => 500000,
                    'net_income' => 17000000,
                ],
                '2022-02' => [
                    'total_income' => 18000000,
                    'total_expense' => 3500000,
                    'net_income' => 14500000,
                ],
                '2022-03' => [
                    'total_income' => 15500000,
                    'total_expense' => 4500000,
                    'net_income' => 11000000,
                ],
            ],
            'summary' => [
                'total_income' => 51000000,
                'total_expense' => 8500000,
                'net_income' => 42500000,
            ],
        ];

        $export = new ProfitAndLossExport($sampleReport);
        $rows = $export->array();

        $this->assertSame(['Category', '2022-01', '2022-02', '2022-03'], $rows[0]);
        $this->assertSame(['', 'Amount', 'Amount', 'Amount'], $rows[1]);
        $this->assertSame(['Salary', 12000000.0, 12000000.0, 12000000.0], $rows[2]);
        $this->assertSame(['Other Income', 5500000.0, 6000000.0, 3500000.0], $rows[3]);
        $this->assertSame(['Total Income', 17500000.0, 18000000.0, 15500000.0], $rows[4]);
        $this->assertSame(['Family Expense', 500000.0, 3500000.0, 4500000.0], $rows[5]);
        $this->assertSame(['Total Expense', 500000.0, 3500000.0, 4500000.0], $rows[6]);
        $this->assertSame(['Net Income', 17000000.0, 14500000.0, 11000000.0], $rows[7]);
    }
}
