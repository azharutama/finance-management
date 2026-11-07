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
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    public function index(TransactionFilterRequest $request): JsonResponse
    {
        $paginator = $this->transactionService->list($request->validated());

        return ApiResponse::paginated(
            $paginator,
            'Transactions retrieved successfully.'
        );
    }

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

    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $updated = $this->transactionService->update($transaction, $request->validated());

        return ApiResponse::success(
            $updated,
            'Transaction updated successfully.'
        );
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        $this->transactionService->delete($transaction);

        return ApiResponse::success(null, 'Transaction deleted successfully.');
    }
}
