<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $service;

    /**
     * Mempersiapkan instance CategoryService sebelum setiap pengujian.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CategoryService::class);
    }

    /**
     * Memastikan metode create berhasil menyimpan data kategori ke database.
     */
    public function test_create_category(): void
    {
        $category = $this->service->create(['name' => 'Salary']);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Salary', $category->name);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Salary']);
    }

    /**
     * Memastikan metode update berhasil memperbarui nama kategori.
     */
    public function test_update_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);
        $updated = $this->service->update($category, ['name' => 'New Name']);

        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'New Name']);
    }

    /**
     * Memastikan kategori tanpa COA berhasil dihapus secara fisik.
     */
    public function test_delete_category_without_coa_succeeds(): void
    {
        $category = Category::factory()->create();
        $this->service->delete($category);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /**
     * Memastikan kategori berelasi ke COA menolak penghapusan dengan status 409.
     */
    public function test_delete_category_with_coa_aborts_with_conflict(): void
    {
        $category = Category::factory()->create();
        ChartOfAccount::factory()->create(['category_id' => $category->id]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->service->delete($category);
    }

    /**
     * Memastikan metode list mendukung paginasi dan pengambilan seluruh baris (all=true).
     */
    public function test_list_and_all_categories(): void
    {
        Category::factory()->count(3)->create();

        $paginated = $this->service->list(['per_page' => 2]);
        $this->assertCount(2, $paginated->items());

        $all = $this->service->list(['all' => true]);
        $this->assertCount(3, $all);
    }
}

