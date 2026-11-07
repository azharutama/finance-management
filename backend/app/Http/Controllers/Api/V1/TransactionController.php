<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\TransactionFilterRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Inisialisasi controller dengan dependency TransactionService.
     */
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    /**
     * Menampilkan daftar transaksi dengan filter COA, kategori, rentang tanggal, dan paginasi.
     */
    public function index(TransactionFilterRequest $request): JsonResponse
    {
        $paginator = $this->transactionService->list($request->validated());

        return ApiResponse::paginated(
            $paginator,
            'Transactions retrieved successfully.'
        );
    }

    /**
     * Menyimpan pencatatan transaksi baru (debit atau credit).
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactionService->create(
            $request->validated(),
            $request->user()?->id
        );

        return ApiResponse::created(
            $transaction,
            'Transaction created successfully.'
        );
    }

    /**
     * Menampilkan detail satu transaksi beserta relasi COA dan kategorinya.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load([
            'coa:id,code,name,category_id',
            'coa.category:id,name',
        ]);

        return ApiResponse::success(
            $transaction,
            'Transaction retrieved successfully.'
        );
    }

    /**
     * Memperbarui data transaksi yang sudah dicatat sebelumnya.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $updated = $this->transactionService->update($transaction, $request->validated());

        return ApiResponse::success(
            $updated,
            'Transaction updated successfully.'
        );
    }

    /**
     * Menghapus transaksi menggunakan Soft Delete agar riwayat audit keuangan tetap aman.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $this->transactionService->delete($transaction);

        return ApiResponse::success(null, 'Transaction deleted successfully.');
    }
}
