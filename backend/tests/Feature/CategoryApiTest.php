<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChartOfAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_create_update_and_delete_category(): void
    {
        // Create
        $createResponse = $this->postJson('/api/v1/categories', [
            'name' => 'Salary',
        ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Salary')
            ->assertJsonMissingPath('data.type');

        $categoryId = $createResponse->json('data.id');

        // Duplicate name fails with 422
        $this->postJson('/api/v1/categories', [
            'name' => 'Salary',
        ])->assertStatus(422)
            ->assertJsonPath('success', false);

        // List paginated
        $this->getJson('/api/v1/categories')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total', 'last_page']]);

        // Update
        $this->putJson("/api/v1/categories/{$categoryId}", [
            'name' => 'Monthly Salary',
        ])->assertStatus(200)
            ->assertJsonPath('data.name', 'Monthly Salary');

        // Delete unused category
        $this->deleteJson("/api/v1/categories/{$categoryId}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
    }

    public function test_cannot_delete_category_that_has_coa(): void
    {
        $category = Category::factory()->expense()->create(['name' => 'Transport Expense']);
        ChartOfAccount::factory()->create([
            'code' => '602',
            'name' => 'Bensin',
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 409);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
