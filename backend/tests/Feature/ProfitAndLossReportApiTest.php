<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ProfitAndLossReportApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memverifikasi kalkulasi Profit & Loss per bulan dan fungsionalitas unduh Excel.
     */
    public function test_it_calculates_profit_and_loss_per_month_including_empty_months_and_exports_excel_with_maatwebsite(): void
    {
        Excel::fake();

        $salaryCat = Category::factory()->income()->create(['name' => 'Salary']);
        $otherIncomeCat = Category::factory()->income()->create(['name' => 'Other Income']);
        $transportCat = Category::factory()->expense()->create(['name' => 'Transport Expense']);
        $mealCat = Category::factory()->expense()->create(['name' => 'Meal Expense']);
        $unusedCat = Category::factory()->expense()->create(['name' => 'Unused Expense']);

        $coaSalary = ChartOfAccount::factory()->create(['code' => '401', 'name' => 'Gaji Karyawan', 'category_id' => $salaryCat->id]);
        $coaTrading = ChartOfAccount::factory()->create(['code' => '403', 'name' => 'Profit Trading', 'category_id' => $otherIncomeCat->id]);
        $coaFuel = ChartOfAccount::factory()->create(['code' => '602', 'name' => 'Bensin', 'category_id' => $transportCat->id]);
        $coaLunch = ChartOfAccount::factory()->create(['code' => '604', 'name' => 'Makan Siang', 'category_id' => $mealCat->id]);
        ChartOfAccount::factory()->create(['code' => '699', 'name' => 'Unused COA', 'category_id' => $unusedCat->id]);

        // Jan 2022 transactions
        Transaction::factory()->create([
            'date' => '2022-01-05',
            'coa_id' => $coaSalary->id,
            'description' => 'Gaji Jan',
            'debit' => 0,
            'credit' => 12000000,
        ]);
        Transaction::factory()->create([
            'date' => '2022-01-10',
            'coa_id' => $coaFuel->id,
            'description' => 'Bensin Jan',
            'debit' => 25000,
            'credit' => 0,
        ]);
        Transaction::factory()->create([
            'date' => '2022-01-12',
            'coa_id' => $coaLunch->id,
            'description' => 'Makan Jan',
            'debit' => 45000,
            'credit' => 0,
        ]);

        // Feb 2022 is intentionally empty to test zero-filled months!
        // A soft-deleted transaction in Jan 2022 must be completely ignored in report calculations
        $deletedTx = Transaction::factory()->create([
            'date' => '2022-01-15',
            'coa_id' => $coaLunch->id,
            'description' => 'Makan Deleted',
            'debit' => 999999,
            'credit' => 0,
        ]);
        $deletedTx->delete();

        // Mar 2022 transactions
        Transaction::factory()->create([
            'date' => '2022-03-01',
            'coa_id' => $coaSalary->id,
            'description' => 'Gaji Mar',
            'debit' => 0,
            'credit' => 5000000,
        ]);
        Transaction::factory()->create([
            'date' => '2022-03-05',
            'coa_id' => $coaTrading->id,
            'description' => 'Trading Mar',
            'debit' => 0,
            'credit' => 1500000,
        ]);
        Transaction::factory()->create([
            'date' => '2022-03-10',
            'coa_id' => $coaFuel->id,
            'description' => 'Bensin Mar',
            'debit' => 300000,
            'credit' => 0,
        ]);

        $response = $this->getJson('/api/v1/reports/profit-loss?start_month=2022-01&end_month=2022-03');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.income_categories')
            ->assertJsonCount(2, 'data.expense_categories')
            ->assertJsonPath('data.months', ['2022-01', '2022-02', '2022-03'])
            ->assertJsonPath('data.totals_by_month.2022-01.total_income', 12000000)
            ->assertJsonPath('data.totals_by_month.2022-01.total_expense', 70000)
            ->assertJsonPath('data.totals_by_month.2022-01.net_income', 11930000)
            ->assertJsonPath('data.totals_by_month.2022-02.total_income', 0)
            ->assertJsonPath('data.totals_by_month.2022-02.total_expense', 0)
            ->assertJsonPath('data.totals_by_month.2022-02.net_income', 0)
            ->assertJsonPath('data.totals_by_month.2022-03.total_income', 6500000)
            ->assertJsonPath('data.totals_by_month.2022-03.total_expense', 300000)
            ->assertJsonPath('data.totals_by_month.2022-03.net_income', 6200000)
            ->assertJsonPath('data.summary.total_income', 18500000)
            ->assertJsonPath('data.summary.total_expense', 370000)
            ->assertJsonPath('data.summary.net_income', 18130000);

        // Test Excel export endpoint using Maatwebsite\Excel
        $exportResponse = $this->get('/api/v1/reports/profit-loss/export?start_month=2022-01&end_month=2022-03');

        $exportResponse->assertStatus(200);
        Excel::assertDownloaded('profit-loss-report-2022-01-to-2022-03.xlsx');
    }

    /**
     * Memastikan jika satu kategori memiliki transaksi Credit sekaligus Debit,
     * Credit dihitung pada Income per kategori dan Debit dihitung pada Expense per kategori.
     */
    public function test_it_separates_credit_into_income_and_debit_into_expense_per_category(): void
    {
        $gajiCat = Category::factory()->create(['name' => 'Gaji DPR']);
        $transportCat = Category::factory()->create(['name' => 'Transport']);

        $coaIncome = ChartOfAccount::factory()->create(['code' => '123', 'name' => 'income trading', 'category_id' => $gajiCat->id]);
        $coaGaji = ChartOfAccount::factory()->create(['code' => '222', 'name' => 'GAji DPR', 'category_id' => $gajiCat->id]);
        $coaBensin = ChartOfAccount::factory()->create(['code' => '789', 'name' => 'bensin', 'category_id' => $transportCat->id]);

        // Credit & Debit pada kategori Gaji DPR
        Transaction::factory()->create(['date' => '2026-11-07', 'coa_id' => $coaGaji->id, 'description' => 'gaji dpr', 'debit' => 0, 'credit' => 10000000]);
        Transaction::factory()->create(['date' => '2026-11-07', 'coa_id' => $coaGaji->id, 'description' => 'refund gaji', 'debit' => 10000, 'credit' => 0]);
        Transaction::factory()->create(['date' => '2026-11-08', 'coa_id' => $coaIncome->id, 'description' => 'income', 'debit' => 0, 'credit' => 123232]);
        Transaction::factory()->create(['date' => '2026-11-13', 'coa_id' => $coaIncome->id, 'description' => 'profit', 'debit' => 0, 'credit' => 11111111]);

        // Credit & Debit pada kategori Transport
        Transaction::factory()->create(['date' => '2026-11-08', 'coa_id' => $coaBensin->id, 'description' => 'bensin anak', 'debit' => 40000, 'credit' => 0]);
        Transaction::factory()->create(['date' => '2026-11-08', 'coa_id' => $coaBensin->id, 'description' => 'kembalian bensin', 'debit' => 0, 'credit' => 100000]);

        $response = $this->getJson('/api/v1/reports/profit-loss?start_month=2026-11&end_month=2026-12');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data.income_categories')
            ->assertJsonCount(2, 'data.expense_categories')
            ->assertJsonPath('data.income_categories.0.category_name', 'Gaji DPR')
            ->assertJsonPath('data.income_categories.0.monthly.2026-11', 21234343)
            ->assertJsonPath('data.income_categories.1.category_name', 'Transport')
            ->assertJsonPath('data.income_categories.1.monthly.2026-11', 100000)
            ->assertJsonPath('data.totals_by_month.2026-11.total_income', 21334343)
            ->assertJsonPath('data.expense_categories.0.category_name', 'Gaji DPR')
            ->assertJsonPath('data.expense_categories.0.monthly.2026-11', 10000)
            ->assertJsonPath('data.expense_categories.1.category_name', 'Transport')
            ->assertJsonPath('data.expense_categories.1.monthly.2026-11', 40000)
            ->assertJsonPath('data.totals_by_month.2026-11.total_expense', 50000)
            ->assertJsonPath('data.totals_by_month.2026-11.net_income', 21284343);
    }
}
