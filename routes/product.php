<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/product', [ProductController::class, 'getProducts']);
    Route::get('/product/{id}', [ProductController::class, 'getProduct']);
    Route::post('/product/store', [ProductController::class, 'storeProduct']);
    Route::post('/product/update', [ProductController::class, 'updateProduct']);
    Route::post('/product/delete', [ProductController::class, 'deleteProduct']);
    Route::get('/product-by-barcode', [ProductController::class, 'getProductByBarcode']);
    Route::get('/products-with-stock', [ProductController::class, 'getProductsWithStock']);
});
