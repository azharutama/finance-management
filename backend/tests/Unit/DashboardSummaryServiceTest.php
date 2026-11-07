<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Services\DashboardSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardSummaryService $service;

    /**
     * Mempersiapkan instance DashboardSummaryService sebelum pengujian unit.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DashboardSummaryService::class);
    }

    /**
     * Memverifikasi kalkulasi agregat ringkasan KPI (income, expense, net income, count) bulanan.
     */
    public function test_get_monthly_summary_aggregates_correctly(): void
    {
        $cat = Category::factory()->create();
        $coa = ChartOfAccount::factory()->create(['category_id' => $cat->id]);

        // 2 credit transactions in 2026-05
        Transaction::factory()->create([
            'coa_id' => $coa->id,
            'date' => '2026-05-02',
            'credit' => 5000000,
            'debit' => 0,
        ]);
        Transaction::factory()->create([
            'coa_id' => $coa->id,
            'date' => '2026-05-05',
            'credit' => 2000000,
            'debit' => 0,
        ]);

        // 1 debit transaction in 2026-05
        Transaction::factory()->create([
            'coa_id' => $coa->id,
            'date' => '2026-05-10',
            'credit' => 0,
            'debit' => 1500000,
        ]);

        // 1 transaction in different month (should be excluded)
        Transaction::factory()->create([
            'coa_id' => $coa->id,
            'date' => '2026-06-01',
            'credit' => 10000000,
            'debit' => 0,
        ]);

        // 1 soft-deleted transaction in 2026-05 (should be excluded)
        $deletedTx = Transaction::factory()->create([
            'coa_id' => $coa->id,
            'date' => '2026-05-15',
            'credit' => 9999999,
            'debit' => 0,
        ]);
        $deletedTx->delete();

        $summary = $this->service->summarize('2026-05');

        $this->assertEquals('2026-05', $summary['month']);
        $this->assertEquals(7000000, $summary['total_income']);
        $this->assertEquals(1500000, $summary['total_expense']);
        $this->assertEquals(5500000, $summary['net_income']);
        $this->assertEquals(2, $summary['credit_count']);
        $this->assertEquals(1, $summary['debit_count']);
    }
}

