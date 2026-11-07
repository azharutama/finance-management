<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    /**
     * Retrieve paginated or all categories with COA counts.
     */
    public function list(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = Category::query()
            ->select(['id', 'name', 'type', 'created_at', 'updated_at'])
            ->withCount('chartOfAccounts');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $query->orderByRaw("CASE WHEN type = 'income' THEN 0 ELSE 1 END")
            ->orderBy('name');

        if (! empty($filters['all'])) {
            return $query->get();
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->paginate($perPage);
    }

    /**
     * Create a new category inside a database transaction.
     */
    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $type = $data['type'] ?? $this->inferTypeFromName($data['name']);

            $category = Category::create([
                'name' => $data['name'],
                'type' => $type,
            ]);

            return $category->loadCount('chartOfAccounts');
        });
    }

    /**
     * Update an existing category safely with row locking.
     */
    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            /** @var Category $locked */
            $locked = Category::whereKey($category->id)->lockForUpdate()->firstOrFail();

            $newType = $data['type'] ?? $locked->type;
            if ($newType !== $locked->type && $locked->transactions()->exists()) {
                abort(409, 'Cannot change category type because transactions have already been recorded under this category.');
            }

            $locked->update([
                'name' => $data['name'],
                'type' => $newType,
            ]);

            return $locked->loadCount('chartOfAccounts');
        });
    }

    /**
     * Delete a category only if it is not referenced by any COA.
     */
    public function delete(Category $category): void
    {
        DB::transaction(function () use ($category) {
            /** @var Category $locked */
            $locked = Category::whereKey($category->id)->lockForUpdate()->firstOrFail();

            if ($locked->chartOfAccounts()->exists()) {
                abort(409, 'Cannot delete category that is assigned to one or more Chart of Accounts.');
            }

            $locked->delete();
        });
    }

    /**
     * Infer category classification (income/expense) by category name.
     */
    private function inferTypeFromName(string $name): string
    {
        $lower = strtolower($name);
        if (str_contains($lower, 'income') || str_contains($lower, 'gaji') || str_contains($lower, 'salary') || str_contains($lower, 'pendapatan') || str_contains($lower, 'profit')) {
            return Category::TYPE_INCOME;
        }

        return Category::TYPE_EXPENSE;
    }
}
