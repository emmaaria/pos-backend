<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::post('login', [\App\Http\Controllers\ApiController::class, 'login']);
    Route::get('/profile', [\App\Http\Controllers\ApiController::class, 'profile']);
    Route::get('/category', [\App\Http\Controllers\ApiController::class, 'getCategories']);
    Route::get('/category/{id}', [\App\Http\Controllers\ApiController::class, 'getCategory']);
    Route::post('/category/store', [\App\Http\Controllers\ApiController::class, 'storeCategory']);
    Route::post('/category/update', [\App\Http\Controllers\ApiController::class, 'updateCategory']);
    Route::post('/category/delete', [\App\Http\Controllers\ApiController::class, 'deleteCategory']);

    Route::get('/unit', [\App\Http\Controllers\ApiController::class, 'getUnits']);
    Route::get('/unit/{id}', [\App\Http\Controllers\ApiController::class, 'getUnit']);
    Route::post('/unit/store', [\App\Http\Controllers\ApiController::class, 'storeUnit']);
    Route::post('/unit/update', [\App\Http\Controllers\ApiController::class, 'updateUnit']);
    Route::post('/unit/delete', [\App\Http\Controllers\ApiController::class, 'deleteUnit']);

    Route::get('/customer', [\App\Http\Controllers\ApiController::class, 'getCustomers']);
    Route::get('/customer/{id}', [\App\Http\Controllers\ApiController::class, 'getCustomer']);
    Route::post('/customer/store', [\App\Http\Controllers\ApiController::class, 'storeCustomer']);
    Route::post('/customer/update', [\App\Http\Controllers\ApiController::class, 'updateCustomer']);
    Route::post('/customer/delete', [\App\Http\Controllers\ApiController::class, 'deleteCustomer']);

    Route::get('/supplier', [\App\Http\Controllers\ApiController::class, 'getSuppliers']);
    Route::get('/supplier/{id}', [\App\Http\Controllers\ApiController::class, 'getSupplier']);
    Route::post('/supplier/store', [\App\Http\Controllers\ApiController::class, 'storeSupplier']);
    Route::post('/supplier/update', [\App\Http\Controllers\ApiController::class, 'updateSupplier']);
    Route::post('/supplier/delete', [\App\Http\Controllers\ApiController::class, 'deleteSupplier']);

    Route::get('/purchase', [\App\Http\Controllers\ApiController::class, 'getPurchases']);
    Route::get('/purchase/{id}', [\App\Http\Controllers\ApiController::class, 'getPurchase']);
    Route::post('/purchase/store', [\App\Http\Controllers\ApiController::class, 'storePurchase']);
    Route::post('/purchase/update', [\App\Http\Controllers\ApiController::class, 'updatePurchase']);
    Route::post('/purchase/delete', [\App\Http\Controllers\ApiController::class, 'deletePurchase']);

    Route::get('/invoice', [\App\Http\Controllers\ApiController::class, 'getInvoices']);
    Route::get('/invoice/{id}', [\App\Http\Controllers\ApiController::class, 'getInvoice']);
    Route::post('/invoice/store', [\App\Http\Controllers\ApiController::class, 'storeInvoice']);
    Route::post('/invoice/update', [\App\Http\Controllers\ApiController::class, 'updateInvoice']);
    Route::post('/invoice/delete', [\App\Http\Controllers\ApiController::class, 'deleteInvoice']);

    Route::get('/product', [\App\Http\Controllers\ApiController::class, 'getProducts']);
    Route::get('/product/{id}', [\App\Http\Controllers\ApiController::class, 'getProduct']);
    Route::post('/product/store', [\App\Http\Controllers\ApiController::class, 'storeProduct']);
    Route::post('/product/update', [\App\Http\Controllers\ApiController::class, 'updateProduct']);
    Route::post('/product/delete', [\App\Http\Controllers\ApiController::class, 'deleteProduct']);
    Route::get('/product-by-barcode', [\App\Http\Controllers\ApiController::class, 'getProductByBarcode']);

    Route::get('/stock', [\App\Http\Controllers\ApiController::class, 'getStock']);

    Route::get('/bank', [\App\Http\Controllers\ApiController::class, 'getBanks']);
    Route::get('/bank/{id}', [\App\Http\Controllers\ApiController::class, 'getBank']);
    Route::post('/bank/store', [\App\Http\Controllers\ApiController::class, 'storeBank']);
    Route::post('/bank/update', [\App\Http\Controllers\ApiController::class, 'updateBank']);
    Route::post('/bank/delete', [\App\Http\Controllers\ApiController::class, 'deleteBank']);

    Route::get('/company', [\App\Http\Controllers\ApiController::class, 'getCompany']);
    Route::post('/company/update', [\App\Http\Controllers\ApiController::class, 'updateCompany']);
});
