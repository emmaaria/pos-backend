<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/customer', [CustomerController::class, 'getCustomers']);
    Route::get('/customer/{id}', [CustomerController::class, 'getCustomer']);
    Route::post('/customer/store', [CustomerController::class, 'storeCustomer']);
    Route::post('/customer/update', [CustomerController::class, 'updateCustomer']);
    Route::post('/customer/delete', [CustomerController::class, 'deleteCustomer']);
    Route::post('/customer/payment/store', [CustomerController::class, 'storePayment']);
    Route::post('/customer/payment/delete', [CustomerController::class, 'deletePayment']);
    Route::post('/customer/payments', [CustomerController::class, 'customerPaymentList']);
    Route::post('/customer/dues', [CustomerController::class, 'customerDueList']);
});
