<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Services\ChartOfAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountServiceTest extends TestCase
{
    use RefreshDatabase;

    private ChartOfAccountService $service;

    /**
     * Mempersiapkan instance ChartOfAccountService sebelum setiap pengujian.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ChartOfAccountService::class);
    }

    /**
     * Memastikan metode create berhasil membuat akun COA baru.
     */
    public function test_create_coa(): void
    {
        $category = Category::factory()->create();
        $coa = $this->service->create([
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $category->id,
        ]);

        $this->assertInstanceOf(ChartOfAccount::class, $coa);
        $this->assertEquals('401', $coa->code);
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $coa->id, 'code' => '401']);
    }

    /**
     * Memastikan metode update berhasil memperbarui nama akun COA.
     */
    public function test_update_coa(): void
    {
        $category = Category::factory()->create();
        $coa = ChartOfAccount::factory()->create(['category_id' => $category->id, 'name' => 'Old Name']);

        $updated = $this->service->update($coa, [
            'name' => 'New Name',
        ]);

        $this->assertEquals('New Name', $updated->name);
    }

    /**
     * Memastikan akun COA yang belum terpakai di transaksi berhasil dihapus.
     */
    public function test_delete_unused_coa_succeeds(): void
    {
        $category = Category::factory()->create();
        $coa = ChartOfAccount::factory()->create(['category_id' => $category->id]);

        $this->service->delete($coa);
        $this->assertDatabaseMissing('chart_of_accounts', ['id' => $coa->id]);
    }

    /**
     * Memastikan akun COA yang memiliki transaksi menolak penghapusan dengan 409 Conflict.
     */
    public function test_delete_coa_used_in_transactions_aborts_with_conflict(): void
    {
        $category = Category::factory()->create();
        $coa = ChartOfAccount::factory()->create(['category_id' => $category->id]);
        Transaction::factory()->create(['coa_id' => $coa->id]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->delete($coa);
    }

    /**
     * Memastikan metode list COA mendukung paginasi dan filter kategori.
     */
    public function test_list_and_all_coa(): void
    {
        $category = Category::factory()->create();
        ChartOfAccount::factory()->count(4)->create(['category_id' => $category->id]);

        $paginated = $this->service->list(['category_id' => $category->id, 'per_page' => 2]);
        $this->assertCount(2, $paginated->items());

        $all = $this->service->list(['all' => true]);
        $this->assertCount(4, $all);
    }
}

