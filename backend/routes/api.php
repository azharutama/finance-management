<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ChartOfAccountController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Authentication Endpoint with Rate Limiting
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:auth')
        ->name('api.v1.auth.login');

    // Protected API Endpoints
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])
            ->name('api.v1.auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])
            ->name('api.v1.auth.me');

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
});
