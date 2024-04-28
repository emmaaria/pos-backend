<?php

use App\Http\Controllers\SaleReturnController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Invoice API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/return/customer', [SaleReturnController::class, 'getReturns']);
    Route::get('/return/customer/{id}', [SaleReturnController::class, 'getReturn']);
    Route::post('/return/customer/store', [SaleReturnController::class, 'storeReturn']);
    Route::post('/return/customer/delete', [SaleReturnController::class, 'delete']);
});
