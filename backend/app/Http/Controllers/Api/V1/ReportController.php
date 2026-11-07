<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\ProfitAndLossExport;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardSummaryRequest;
use App\Http\Requests\ProfitLossReportRequest;
use App\Services\DashboardSummaryService;
use App\Services\ProfitAndLossService;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    /**
     * Inisialisasi controller dengan ProfitAndLossService dan DashboardSummaryService.
     */
    public function __construct(
        private readonly ProfitAndLossService $profitAndLossService,
        private readonly DashboardSummaryService $dashboardSummaryService
    ) {}

    /**
     * Menghasilkan ringkasan KPI dashboard (total income, total expense, net income, count) bulanan.
     */
    public function dashboardSummary(DashboardSummaryRequest $request): JsonResponse
    {
        $summary = $this->dashboardSummaryService->summarize($request->validated('month'));

        return ApiResponse::success(
            $summary,
            'Dashboard summary generated successfully.'
        );
    }

    /**
     * Menghasilkan laporan laba rugi (Profit & Loss) bulanan sesuai struktur format spreadsheet.
     */
    public function profitAndLoss(ProfitLossReportRequest $request): JsonResponse
    {
        $report = $this->profitAndLossService->generateReport($request->validated());

        return ApiResponse::success(
            $report,
            'Profit & Loss report generated successfully.'
        );
    }

    /**
     * Mengekspor data laporan laba rugi ke dalam file spreadsheet Excel (.xlsx).
     */
    public function exportProfitAndLoss(ProfitLossReportRequest $request): BinaryFileResponse
    {
        $report = $this->profitAndLossService->generateReport($request->validated());

        $startMonth = $report['period']['start_month'] ?? 'start';
        $endMonth = $report['period']['end_month'] ?? 'end';
        $filename = "profit-loss-report-{$startMonth}-to-{$endMonth}.xlsx";

        return Excel::download(new ProfitAndLossExport($report), $filename);
    }
}
