<?php

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ChartOfAccountController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::apiResource('categories', CategoryController::class)
        ->names('api.v1.categories');

    Route::apiResource('coa', ChartOfAccountController::class)
        ->parameters(['coa' => 'coa'])
        ->names('api.v1.coa');

    Route::apiResource('transactions', TransactionController::class)
        ->names('api.v1.transactions');

    Route::get('/reports/profit-loss', [ReportController::class, 'profitAndLoss'])
        ->name('api.v1.reports.profit-loss');

    Route::get('/reports/profit-loss/export', [ReportController::class, 'exportProfitAndLoss'])
        ->name('api.v1.reports.profit-loss.export');
});
