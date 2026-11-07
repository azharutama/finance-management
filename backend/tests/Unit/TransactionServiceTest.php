<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $service;

    /**
     * Mempersiapkan instance TransactionService sebelum setiap pengujian.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TransactionService::class);
    }

    /**
     * Memastikan metode create berhasil mencatat transaksi baru.
     */
    public function test_create_transaction(): void
    {
        $category = Category::factory()->create();
        $coa = ChartOfAccount::factory()->create(['category_id' => $category->id]);

        $tx = $this->service->create([
            'date' => '2026-02-01',
            'coa_id' => $coa->id,
            'description' => 'Test Service Tx',
            'debit' => 0,
            'credit' => 1000000,
        ]);

        $this->assertInstanceOf(Transaction::class, $tx);
        $this->assertEquals(1000000, $tx->credit);
        $this->assertDatabaseHas('transactions', ['id' => $tx->id, 'credit' => 1000000]);
    }

    /**
     * Memastikan metode update berhasil memperbarui deskripsi transaksi.
     */
    public function test_update_transaction(): void
    {
        $category = Category::factory()->create();
        $coa = ChartOfAccount::factory()->create(['category_id' => $category->id]);
        $tx = Transaction::factory()->create(['coa_id' => $coa->id, 'description' => 'Before']);

        $updated = $this->service->update($tx, [
            'description' => 'After',
        ]);

        $this->assertEquals('After', $updated->description);
    }

    /**
     * Memastikan penghapusan transaksi menerapkan mekanisme Soft Delete.
     */
    public function test_delete_transaction_uses_soft_delete(): void
    {
        $category = Category::factory()->create();
        $coa = ChartOfAccount::factory()->create(['category_id' => $category->id]);
        $tx = Transaction::factory()->create(['coa_id' => $coa->id]);

        $this->service->delete($tx);

        $this->assertSoftDeleted('transactions', ['id' => $tx->id]);
    }

    /**
     * Memastikan metode list transaksi mendukung filter pencarian dan rentang tanggal.
     */
    public function test_list_transactions_with_filters(): void
    {
        $category = Category::factory()->create();
        $coa = ChartOfAccount::factory()->create(['category_id' => $category->id]);

        Transaction::factory()->create([
            'coa_id' => $coa->id,
            'date' => '2026-03-01',
            'description' => 'Searchable Keyword',
        ]);
        Transaction::factory()->create([
            'coa_id' => $coa->id,
            'date' => '2026-04-01',
            'description' => 'Other Item',
        ]);

        $result = $this->service->list(['search' => 'Searchable', 'start_date' => '2026-03-01', 'end_date' => '2026-03-31']);
        $this->assertCount(1, $result->items());
    }
}

