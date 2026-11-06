<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\ProfitAndLossExport;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfitLossReportRequest;
use App\Services\ProfitAndLossService;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ProfitAndLossService $profitAndLossService
    ) {}

    public function profitAndLoss(ProfitLossReportRequest $request): JsonResponse
    {
        $report = $this->profitAndLossService->generateReport($request->validated());

        return ApiResponse::success(
            $report,
            'Profit & Loss report generated successfully.'
        );
    }

    public function exportProfitAndLoss(ProfitLossReportRequest $request): BinaryFileResponse
    {
        $report = $this->profitAndLossService->generateReport($request->validated());

        $startMonth = $report['period']['start_month'] ?? 'start';
        $endMonth = $report['period']['end_month'] ?? 'end';
        $filename = "profit-loss-report-{$startMonth}-to-{$endMonth}.xlsx";

        return Excel::download(new ProfitAndLossExport($report), $filename);
    }
}
