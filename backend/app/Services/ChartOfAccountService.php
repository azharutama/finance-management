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
            ->select(['id', 'code', 'name', 'category_id', 'created_at', 'updated_at'])
            ->with(['category:id,name'])
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
            ]);

            return $coa->load('category:id,name')->loadCount('transactions');
        });
    }

    /**
     * Update an existing Chart of Account safely with row locking.
     */
    public function update(ChartOfAccount $coa, array $data): ChartOfAccount
    {
        return DB::transaction(function () use ($coa, $data) {
            /** @var ChartOfAccount $locked */
            $locked = ChartOfAccount::whereKey($coa->id)->lockForUpdate()->firstOrFail();

            $newCategoryId = (int) $data['category_id'];

            $locked->update([
                'code' => $data['code'],
                'name' => $data['name'],
                'category_id' => $newCategoryId,
            ]);

            return $locked->load('category:id,name')->loadCount('transactions');
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
