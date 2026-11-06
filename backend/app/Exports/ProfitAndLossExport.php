<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitAndLossExport implements FromArray, WithColumnWidths, WithStyles, WithTitle
{
    /**
     * @param  array<string, mixed>  $report
     */
    public function __construct(
        private readonly array $report
    ) {}

    public function title(): string
    {
        return 'Profit & Loss';
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $months = $this->report['months'] ?? [];
        $incomeCategories = $this->report['income_categories'] ?? [];
        $expenseCategories = $this->report['expense_categories'] ?? [];
        $totalsByMonth = $this->report['totals_by_month'] ?? [];

        $rows = [];

        // Row 1: Category | 2022-01 | 2022-02 | 2022-03 ...
        $headerRow1 = ['Category'];
        foreach ($months as $month) {
            $headerRow1[] = $month;
        }
        $rows[] = $headerRow1;

        // Row 2: (merged with A1) | Amount | Amount | Amount ...
        $headerRow2 = [''];
        foreach ($months as $ignored) {
            $headerRow2[] = 'Amount';
        }
        $rows[] = $headerRow2;

        // Income Category rows
        foreach ($incomeCategories as $category) {
            $row = [$category['category_name']];
            foreach ($months as $month) {
                $row[] = (float) ($category['monthly'][$month] ?? 0);
            }
            $rows[] = $row;
        }

        // Total Income row
        $totalIncomeRow = ['Total Income'];
        foreach ($months as $month) {
            $totalIncomeRow[] = (float) ($totalsByMonth[$month]['total_income'] ?? 0);
        }
        $rows[] = $totalIncomeRow;

        // Expense Category rows
        foreach ($expenseCategories as $category) {
            $row = [$category['category_name']];
            foreach ($months as $month) {
                $row[] = (float) ($category['monthly'][$month] ?? 0);
            }
            $rows[] = $row;
        }

        // Total Expense row
        $totalExpenseRow = ['Total Expense'];
        foreach ($months as $month) {
            $totalExpenseRow[] = (float) ($totalsByMonth[$month]['total_expense'] ?? 0);
        }
        $rows[] = $totalExpenseRow;

        // Net Income row
        $netIncomeRow = ['Net Income'];
        foreach ($months as $month) {
            $netIncomeRow[] = (float) ($totalsByMonth[$month]['net_income'] ?? 0);
        }
        $rows[] = $netIncomeRow;

        return $rows;
    }

    /**
     * @return array<string, int>
     */
    public function columnWidths(): array
    {
        $monthsCount = count($this->report['months'] ?? []);
        $widths = ['A' => 26];

        for ($i = 2; $i <= $monthsCount + 1; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $widths[$colLetter] = 16;
        }

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        $monthsCount = count($this->report['months'] ?? []);
        $incomeCount = count($this->report['income_categories'] ?? []);
        $expenseCount = count($this->report['expense_categories'] ?? []);

        $lastCol = Coordinate::stringFromColumnIndex(max(1, $monthsCount + 1));

        // Merge A1:A2 for "Category" header matching spreadsheet layout
        $sheet->mergeCells('A1:A2');

        $incomeStartRow = 3;
        $incomeEndRow = $incomeStartRow + max(0, $incomeCount - 1);
        $totalIncomeRow = $incomeStartRow + $incomeCount;

        $expenseStartRow = $totalIncomeRow + 1;
        $expenseEndRow = $expenseStartRow + max(0, $expenseCount - 1);
        $totalExpenseRow = $expenseStartRow + $expenseCount;

        $netIncomeRow = $totalExpenseRow + 1;

        // Apply borders to entire table
        $sheet->getStyle("A1:{$lastCol}{$netIncomeRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF808080'],
                ],
            ],
        ]);

        // Header style (Yellow #FFFF00)
        $sheet->getStyle("A1:{$lastCol}2")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFF00'],
            ],
        ]);

        // Income rows style (Light Green #C6E0B4)
        if ($incomeCount > 0) {
            $sheet->getStyle("A{$incomeStartRow}:{$lastCol}{$incomeEndRow}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFC6E0B4'],
                ],
            ]);
        }

        // Total Income row style (Medium Green #A9D08E, Bold)
        $sheet->getStyle("A{$totalIncomeRow}:{$lastCol}{$totalIncomeRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFA9D08E'],
            ],
        ]);

        // Expense rows style (Light Orange #F8CBAD)
        if ($expenseCount > 0) {
            $sheet->getStyle("A{$expenseStartRow}:{$lastCol}{$expenseEndRow}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF8CBAD'],
                ],
            ]);
        }

        // Total Expense row style (Medium Orange #F4B084, Bold)
        $sheet->getStyle("A{$totalExpenseRow}:{$lastCol}{$totalExpenseRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF4B084'],
            ],
        ]);

        // Net Income row style (Bold)
        $sheet->getStyle("A{$netIncomeRow}:{$lastCol}{$netIncomeRow}")->applyFromArray([
            'font' => ['bold' => true],
        ]);

        // Number formatting for amount columns
        if ($monthsCount > 0) {
            $sheet->getStyle("B3:{$lastCol}{$netIncomeRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
        }

        return [];
    }
}
