<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
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
            if (empty($filters['search'])) {
                $cached = Cache::get('categories:all');
                if ($cached instanceof Collection && $cached->isNotEmpty()) {
                    return $cached;
                }

                $items = $query->get();
                if ($items->isNotEmpty()) {
                    Cache::put('categories:all', $items, 3600);
                }

                return $items;
            }

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

            Cache::forget('categories:all');

            return $category;
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

            Cache::forget('categories:all');

            return $locked;
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

            Cache::forget('categories:all');
        });
    }
}
