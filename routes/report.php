<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Purchase API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::post('/report/sales', [ReportController::class, 'sales']);
    Route::post('/report/purchase', [ReportController::class, 'purchase']);
    Route::post('/report/customer/ledger', [ReportController::class, 'customerLedger']);
    Route::post('/report/supplier/ledger', [ReportController::class, 'supplierLedger']);
    Route::post('/report/sales/by-product', [ReportController::class, 'salesByProduct']);
    Route::post('/report/purchase/by-product', [ReportController::class, 'purchaseByProduct']);
});
