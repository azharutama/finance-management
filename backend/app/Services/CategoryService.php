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
            ->select(['id', 'name', 'created_at', 'updated_at'])
            ->withCount('chartOfAccounts');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        $query->orderBy('name');

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
            $category = Category::create([
                'name' => $data['name'],
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

            $locked->update([
                'name' => $data['name'] ?? $locked->name,
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
}
