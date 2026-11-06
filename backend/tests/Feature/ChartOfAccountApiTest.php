<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChartOfAccountApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_crud_chart_of_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

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

    public function test_cannot_delete_coa_used_in_transactions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

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

