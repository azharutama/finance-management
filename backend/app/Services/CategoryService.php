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
            ->select(['id', 'name', 'type', 'is_active', 'created_at', 'updated_at'])
            ->withCount('chartOfAccounts');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
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
                'is_active' => $data['is_active'] ?? true,
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
                'is_active' => array_key_exists('is_active', $data) && $data['is_active'] !== null
                    ? (bool) $data['is_active']
                    : $locked->is_active,
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
                abort(409, 'Cannot delete category because it is already associated with Chart of Accounts.');
            }

            $locked->delete();
        });
    }

    private function inferTypeFromName(string $name): string
    {
        $lower = strtolower($name);
        foreach (['income', 'salary', 'revenue', 'pendapatan', 'pemasukan', 'gaji'] as $keyword) {
            if (str_contains($lower, $keyword)) {
                return Category::TYPE_INCOME;
            }
        }

        return Category::TYPE_EXPENSE;
    }
}
