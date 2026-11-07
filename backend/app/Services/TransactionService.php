<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    /**
     * Paginate transactions with eager loading and filtering.
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Transaction::query()
            ->select(['id', 'date', 'coa_id', 'description', 'debit', 'credit', 'created_at', 'updated_at'])
            ->with([
                'coa:id,code,name,category_id',
                'coa.category:id,name',
            ]);

        if (! empty($filters['start_date'])) {
            $query->whereDate('date', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('date', '<=', $filters['end_date']);
        }

        if (! empty($filters['coa_id'])) {
            $query->where('coa_id', (int) $filters['coa_id']);
        }

        if (! empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];
            $query->whereHas('coa', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('coa', function ($coaQuery) use ($search) {
                        $coaQuery->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        $query->orderByDesc('date')->orderByDesc('id');

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query->paginate($perPage);
    }

    /**
     * Create a new transaction with concurrency locking on the referenced COA.
     */
    public function create(array $data, ?int $userId = null): Transaction
    {
        return DB::transaction(function () use ($data, $userId) {
            /** @var ChartOfAccount|null $coa */
            $coa = ChartOfAccount::with('category')->whereKey($data['coa_id'])->lockForUpdate()->first();

            $transaction = Transaction::create([
                'date' => $data['date'],
                'coa_id' => $coa->id,
                'description' => $data['description'],
                'debit' => round((float) ($data['debit'] ?? 0), 2),
                'credit' => round((float) ($data['credit'] ?? 0), 2),
            ]);

            return $transaction->load([
                'coa:id,code,name,category_id',
                'coa.category:id,name',
            ]);
        });
    }

    /**
     * Update an existing transaction inside a database transaction with row locking.
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            /** @var ChartOfAccount $coa */
            $coa = ChartOfAccount::with('category')->whereKey($data['coa_id'])->lockForUpdate()->firstOrFail();

            /** @var Transaction $locked */
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'date' => $data['date'],
                'coa_id' => $coa->id,
                'description' => $data['description'],
                'debit' => round((float) ($data['debit'] ?? 0), 2),
                'credit' => round((float) ($data['credit'] ?? 0), 2),
            ]);

            return $locked->load([
                'coa:id,code,name,category_id',
                'coa.category:id,name',
            ]);
        });
    }

    /**
     * Delete a transaction atomically.
     */
    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            /** @var Transaction $locked */
            $locked = Transaction::whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            $locked->delete();
        });
    }
}
