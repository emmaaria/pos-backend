<?php

use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Supplier API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/supplier', [SupplierController::class, 'getSuppliers']);
    Route::get('/supplier/{id}', [SupplierController::class, 'getSupplier']);
    Route::post('/supplier/store', [SupplierController::class, 'storeSupplier']);
    Route::post('/supplier/update', [SupplierController::class, 'updateSupplier']);
    Route::post('/supplier/delete', [SupplierController::class, 'deleteSupplier']);
    Route::post('/supplier/payments', [SupplierController::class, 'paymentList']);
    Route::post('/supplier/payment/store', [SupplierController::class, 'storePayment']);
});
