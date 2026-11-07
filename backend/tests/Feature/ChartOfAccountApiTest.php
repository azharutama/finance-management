<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memastikan siklus CRUD COA (create, show, list filter, update, delete) berjalan dengan benar.
     */
    public function test_can_crud_chart_of_account(): void
    {
        $category = Category::factory()->income()->create(['name' => 'Salary']);

        // Create COA
        $createResponse = $this->postJson('/api/v1/coa', [
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $category->id,
        ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', '401')
            ->assertJsonPath('data.category.name', 'Salary');

        $coaId = $createResponse->json('data.id');

        // Show single COA
        $this->getJson("/api/v1/coa/{$coaId}")
            ->assertStatus(200)
            ->assertJsonPath('data.code', '401')
            ->assertJsonPath('data.name', 'Gaji Karyawan');

        // List paginated with search & category filter
        $this->getJson("/api/v1/coa?category_id={$category->id}&search=401")
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        // List all unpaginated
        $allRes = $this->getJson('/api/v1/coa?all=true');
        $allRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta', null);

        // Duplicate code fails with 422
        $this->postJson('/api/v1/coa', [
            'code' => '401',
            'name' => 'Duplicate Code',
            'category_id' => $category->id,
        ])->assertStatus(422);

        // Update COA
        $this->putJson("/api/v1/coa/{$coaId}", [
            'code' => '401',
            'name' => 'Gaji Karyawan Tetap',
            'category_id' => $category->id,
        ])->assertStatus(200)
            ->assertJsonPath('data.name', 'Gaji Karyawan Tetap');

        // Delete unused COA
        $this->deleteJson("/api/v1/coa/{$coaId}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('chart_of_accounts', ['id' => $coaId]);
    }

    /**
     * Memastikan COA dilarang dihapus (409 Conflict) jika sudah pernah digunakan dalam riwayat transaksi.
     */
    public function test_cannot_delete_coa_used_in_transactions(): void
    {
        $category = Category::factory()->expense()->create(['name' => 'Meal Expense']);
        $coa = ChartOfAccount::factory()->create([
            'code' => '604',
            'name' => 'Makan Siang',
            'category_id' => $category->id,
        ]);

        Transaction::factory()->expense(45000)->create([
            'coa_id' => $coa->id,
            'date' => '2026-01-12',
        ]);

        $this->deleteJson("/api/v1/coa/{$coa->id}")
            ->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('chart_of_accounts', ['id' => $coa->id]);
    }
}
