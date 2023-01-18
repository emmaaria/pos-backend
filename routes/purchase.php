<?php

use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Purchase API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/purchase', [PurchaseController::class, 'getPurchases']);
    Route::get('/purchase/{id}', [PurchaseController::class, 'getPurchase']);
    Route::post('/purchase/store', [PurchaseController::class, 'storePurchase']);
    Route::post('/purchase/update', [PurchaseController::class, 'updatePurchase']);
    Route::post('/purchase/delete', [PurchaseController::class, 'deletePurchase']);
});
