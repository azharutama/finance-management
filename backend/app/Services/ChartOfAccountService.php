<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ChartOfAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ChartOfAccountService
{
    /**
     * Retrieve paginated or all Chart of Accounts with eager loaded category.
     */
    public function list(array $filters = []): LengthAwarePaginator|Collection
    {
        $query = ChartOfAccount::query()
            ->select(['id', 'code', 'name', 'category_id', 'is_active', 'created_at', 'updated_at'])
            ->with(['category:id,name,type'])
            ->withCount('transactions');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        $query->orderBy('code');

        if (! empty($filters['all'])) {
            return $query->get();
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->paginate($perPage);
    }

    /**
     * Create a new Chart of Account inside a database transaction.
     */
    public function create(array $data): ChartOfAccount
    {
        return DB::transaction(function () use ($data) {
            $coa = ChartOfAccount::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'category_id' => (int) $data['category_id'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $coa->load('category:id,name,type')->loadCount('transactions');
        });
    }

    /**
     * Update an existing Chart of Account safely with row locking.
     */
    public function update(ChartOfAccount $coa, array $data): ChartOfAccount
    {
        return DB::transaction(function () use ($coa, $data) {
            /** @var ChartOfAccount $locked */
            $locked = ChartOfAccount::with('category')->whereKey($coa->id)->lockForUpdate()->firstOrFail();

            $newCategoryId = (int) $data['category_id'];
            if ($newCategoryId !== (int) $locked->category_id && $locked->transactions()->exists()) {
                $newCategory = Category::findOrFail($newCategoryId);
                if ($locked->category && $newCategory->type !== $locked->category->type) {
                    abort(409, 'Cannot move Chart of Account to a category with a different type because transactions already exist.');
                }
            }

            $locked->update([
                'code' => $data['code'],
                'name' => $data['name'],
                'category_id' => $newCategoryId,
                'is_active' => array_key_exists('is_active', $data) && $data['is_active'] !== null
                    ? (bool) $data['is_active']
                    : $locked->is_active,
            ]);

            return $locked->load('category:id,name,type')->loadCount('transactions');
        });
    }

    /**
     * Delete a Chart of Account only if it is not referenced by any transactions.
     */
    public function delete(ChartOfAccount $coa): void
    {
        DB::transaction(function () use ($coa) {
            /** @var ChartOfAccount $locked */
            $locked = ChartOfAccount::whereKey($coa->id)->lockForUpdate()->firstOrFail();

            if ($locked->transactions()->exists()) {
                abort(409, 'Cannot delete Chart of Account because it has recorded transactions.');
            }

            $locked->delete();
        });
    }
}
