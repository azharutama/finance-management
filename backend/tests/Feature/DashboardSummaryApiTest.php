<?php

namespace Tests\Feature;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSummaryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_sums_credit_and_debit_for_requested_month_only(): void
    {
        $coa = ChartOfAccount::factory()->create();

        // In month 2026-11
        Transaction::factory()->create(['coa_id' => $coa->id, 'date' => '2026-11-02', 'debit' => 0, 'credit' => 5000000]);
        Transaction::factory()->create(['coa_id' => $coa->id, 'date' => '2026-11-03', 'debit' => 0, 'credit' => 2000000]);
        Transaction::factory()->create(['coa_id' => $coa->id, 'date' => '2026-11-04', 'debit' => 250000, 'credit' => 0]);

        // Soft-deleted: ignored
        Transaction::factory()->create(['coa_id' => $coa->id, 'date' => '2026-11-05', 'debit' => 999999, 'credit' => 0])->delete();

        // Other month: ignored
        Transaction::factory()->create(['coa_id' => $coa->id, 'date' => '2026-10-31', 'debit' => 0, 'credit' => 123456]);

        $this->getJson('/api/v1/reports/dashboard-summary?month=2026-11')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.month', '2026-11')
            ->assertJsonPath('data.total_income', 7000000)
            ->assertJsonPath('data.total_expense', 250000)
            ->assertJsonPath('data.net_income', 6750000)
            ->assertJsonPath('data.credit_count', 2)
            ->assertJsonPath('data.debit_count', 1);
    }

    public function test_summary_defaults_to_current_month(): void
    {
        $this->getJson('/api/v1/reports/dashboard-summary')
            ->assertStatus(200)
            ->assertJsonPath('data.month', now()->format('Y-m'));
    }

    public function test_invalid_month_is_rejected(): void
    {
        $this->getJson('/api/v1/reports/dashboard-summary?month=2026-13')
            ->assertStatus(422);
    }
}

