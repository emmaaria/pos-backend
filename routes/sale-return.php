<?php

use App\Http\Controllers\SaleReturnController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Invoice API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/sale/return', [SaleReturnController::class, 'getReturns']);
    Route::get('/sale/return/{id}', [SaleReturnController::class, 'getReturn']);
    Route::post('/sale/return/direct', [SaleReturnController::class, 'storeReturn']);
    Route::post('/sale/return/delete', [SaleReturnController::class, 'delete']);
});
