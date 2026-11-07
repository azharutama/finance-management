<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChartOfAccountFilterRequest;
use App\Http\Requests\StoreChartOfAccountRequest;
use App\Http\Requests\UpdateChartOfAccountRequest;
use App\Models\ChartOfAccount;
use App\Services\ChartOfAccountService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    /**
     * Inisialisasi controller dengan dependency ChartOfAccountService.
     */
    public function __construct(
        private readonly ChartOfAccountService $coaService
    ) {}

    /**
     * Menampilkan daftar COA dengan relasi kategori, filter, dan paginasi.
     */
    public function index(ChartOfAccountFilterRequest $request): JsonResponse
    {
        $result = $this->coaService->list($request->validated());

        if ($result instanceof LengthAwarePaginator) {
            return ApiResponse::paginated(
                $result,
                'Chart of Accounts retrieved successfully.'
            );
        }

        return ApiResponse::success(
            $result,
            'Chart of Accounts retrieved successfully.'
        );
    }

    /**
     * Menyimpan data Chart of Account baru ke database.
     */
    public function store(StoreChartOfAccountRequest $request): JsonResponse
    {
        $coa = $this->coaService->create($request->validated());

        return ApiResponse::created(
            $coa,
            'Chart of Account created successfully.'
        );
    }

    /**
     * Menampilkan detail satu akun COA beserta relasi kategori dan jumlah transaksi.
     */
    public function show(ChartOfAccount $coa): JsonResponse
    {
        $coa->load('category:id,name')->loadCount('transactions');

        return ApiResponse::success(
            $coa,
            'Chart of Account retrieved successfully.'
        );
    }

    /**
     * Memperbarui informasi akun COA (kode, nama, atau kategori).
     */
    public function update(UpdateChartOfAccountRequest $request, ChartOfAccount $coa): JsonResponse
    {
        $updated = $this->coaService->update($coa, $request->validated());

        return ApiResponse::success(
            $updated,
            'Chart of Account updated successfully.'
        );
    }

    /**
     * Menghapus akun COA jika belum pernah digunakan dalam riwayat transaksi.
     */
    public function destroy(ChartOfAccount $coa): JsonResponse
    {
        $this->coaService->delete($coa);

        return ApiResponse::success(null, 'Chart of Account deleted successfully.');
    }
}
