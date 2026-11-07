<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChartOfAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_record_income_and_expense_transactions_and_enforce_debit_credit_rules(): void
    {
        $incomeCategory = Category::factory()->income()->create(['name' => 'Salary']);
        $expenseCategory = Category::factory()->expense()->create(['name' => 'Transport Expense']);

        $incomeCoa = ChartOfAccount::factory()->create([
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $incomeCategory->id,
        ]);

        $expenseCoa = ChartOfAccount::factory()->create([
            'code' => '602',
            'name' => 'Bensin',
            'category_id' => $expenseCategory->id,
        ]);

        // Valid income transaction (credit > 0, debit = 0)
        $incomeTx = $this->postJson('/api/v1/transactions', [
            'date' => '2026-01-01',
            'coa_id' => $incomeCoa->id,
            'description' => 'Gaji Januari',
            'debit' => 0,
            'credit' => 5000000,
        ]);

        $incomeTx->assertStatus(201)
            ->assertJsonPath('data.credit', 5000000)
            ->assertJsonPath('data.debit', 0)
            ->assertJsonPath('data.coa.code', '401');

        // Valid expense transaction (debit > 0, credit = 0)
        $expenseTx = $this->postJson('/api/v1/transactions', [
            'date' => '2026-01-10',
            'coa_id' => $expenseCoa->id,
            'description' => 'Bensin Mobil',
            'debit' => 25000,
            'credit' => 0,
        ]);

        $expenseTx->assertStatus(201)
            ->assertJsonPath('data.debit', 25000)
            ->assertJsonPath('data.credit', 0);

        // Invalid: both debit and credit > 0
        $this->postJson('/api/v1/transactions', [
            'date' => '2026-01-10',
            'coa_id' => $expenseCoa->id,
            'description' => 'Invalid Both',
            'debit' => 25000,
            'credit' => 25000,
        ])->assertStatus(422);

        // Invalid: both debit and credit == 0
        $this->postJson('/api/v1/transactions', [
            'date' => '2026-01-10',
            'coa_id' => $expenseCoa->id,
            'description' => 'Invalid Zero',
            'debit' => 0,
            'credit' => 0,
        ])->assertStatus(422);

        // Invalid: negative nominal
        $this->postJson('/api/v1/transactions', [
            'date' => '2026-01-10',
            'coa_id' => $expenseCoa->id,
            'description' => 'Invalid Negative',
            'debit' => -1000,
            'credit' => 0,
        ])->assertStatus(422);

        // Invalid: debit > 0 on income COA
        $this->postJson('/api/v1/transactions', [
            'date' => '2026-01-10',
            'coa_id' => $incomeCoa->id,
            'description' => 'Wrong side for income',
            'debit' => 500000,
            'credit' => 0,
        ])->assertStatus(422);

        // Filter transactions by category_id and date range
        $listResponse = $this->getJson("/api/v1/transactions?category_id={$expenseCategory->id}&start_date=2026-01-01&end_date=2026-01-31");
        $listResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.description', 'Bensin Mobil');
    }
}
