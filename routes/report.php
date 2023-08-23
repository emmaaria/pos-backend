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
    Route::post('/report/sales/by-category', [ReportController::class, 'salesByCategory']);
    Route::post('/report/purchase/by-product', [ReportController::class, 'purchaseByProduct']);
    Route::post('/report/purchase/by-category', [ReportController::class, 'purchaseByCategory']);
    Route::post('/report/sales/by-customer', [ReportController::class, 'salesByCustomer']);
    Route::post('/report/sales/by-supplier', [ReportController::class, 'salesBySupplier']);
});
