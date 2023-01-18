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
require_once('bank.php');
Route::group(['middleware' => 'api'], function ($router) {
    Route::post('login', [\App\Http\Controllers\ApiController::class, 'login']);
    Route::get('/profile', [\App\Http\Controllers\ApiController::class, 'profile']);

    Route::get('/category', [\App\Http\Controllers\CategoryController::class, 'getCategories']);
    Route::get('/category/{id}', [\App\Http\Controllers\CategoryController::class, 'getCategory']);
    Route::post('/category/store', [\App\Http\Controllers\CategoryController::class, 'storeCategory']);
    Route::post('/category/update', [\App\Http\Controllers\CategoryController::class, 'updateCategory']);
    Route::post('/category/delete', [\App\Http\Controllers\CategoryController::class, 'deleteCategory']);

    Route::get('/unit', [\App\Http\Controllers\UnitController::class, 'getUnits']);
    Route::get('/unit/{id}', [\App\Http\Controllers\UnitController::class, 'getUnit']);
    Route::post('/unit/store', [\App\Http\Controllers\UnitController::class, 'storeUnit']);
    Route::post('/unit/update', [\App\Http\Controllers\UnitController::class, 'updateUnit']);
    Route::post('/unit/delete', [\App\Http\Controllers\UnitController::class, 'deleteUnit']);

    Route::get('/customer', [\App\Http\Controllers\CustomerController::class, 'getCustomers']);
    Route::get('/customer/{id}', [\App\Http\Controllers\CustomerController::class, 'getCustomer']);
    Route::post('/customer/store', [\App\Http\Controllers\CustomerController::class, 'storeCustomer']);
    Route::post('/customer/update', [\App\Http\Controllers\CustomerController::class, 'updateCustomer']);
    Route::post('/customer/delete', [\App\Http\Controllers\CustomerController::class, 'deleteCustomer']);

    Route::get('/supplier', [\App\Http\Controllers\SupplierController::class, 'getSuppliers']);
    Route::get('/supplier/{id}', [\App\Http\Controllers\SupplierController::class, 'getSupplier']);
    Route::post('/supplier/store', [\App\Http\Controllers\SupplierController::class, 'storeSupplier']);
    Route::post('/supplier/update', [\App\Http\Controllers\SupplierController::class, 'updateSupplier']);
    Route::post('/supplier/delete', [\App\Http\Controllers\SupplierController::class, 'deleteSupplier']);

    Route::get('/purchase', [\App\Http\Controllers\PurchaseController::class, 'getPurchases']);
    Route::get('/purchase/{id}', [\App\Http\Controllers\PurchaseController::class, 'getPurchase']);
    Route::post('/purchase/store', [\App\Http\Controllers\PurchaseController::class, 'storePurchase']);
    Route::post('/purchase/update', [\App\Http\Controllers\PurchaseController::class, 'updatePurchase']);
    Route::post('/purchase/delete', [\App\Http\Controllers\PurchaseController::class, 'deletePurchase']);

    Route::get('/invoice', [\App\Http\Controllers\InvoiceController::class, 'getInvoices']);
    Route::get('/today-invoices', [\App\Http\Controllers\InvoiceController::class, 'getTodayInvoices']);
    Route::get('/invoice/{id}', [\App\Http\Controllers\InvoiceController::class, 'getInvoice']);
    Route::post('/invoice/store', [\App\Http\Controllers\InvoiceController::class, 'storeInvoice']);
    Route::post('/invoice/update', [\App\Http\Controllers\InvoiceController::class, 'updateInvoice']);
    Route::post('/invoice/delete', [\App\Http\Controllers\InvoiceController::class, 'deleteInvoice']);

    Route::get('/product', [\App\Http\Controllers\ProductController::class, 'getProducts']);
    Route::get('/product/{id}', [\App\Http\Controllers\ProductController::class, 'getProduct']);
    Route::post('/product/store', [\App\Http\Controllers\ProductController::class, 'storeProduct']);
    Route::post('/product/update', [\App\Http\Controllers\ProductController::class, 'updateProduct']);
    Route::post('/product/delete', [\App\Http\Controllers\ProductController::class, 'deleteProduct']);
    Route::get('/product-by-barcode', [\App\Http\Controllers\ProductController::class, 'getProductByBarcode']);
    Route::get('/products-with-stock', [\App\Http\Controllers\ProductController::class, 'getProductsWithStock']);

    Route::get('/stock', [\App\Http\Controllers\ApiController::class, 'getStock']);

    Route::get('/bank', [\App\Http\Controllers\BankController::class, 'getBanks']);
    Route::get('/bank/{id}', [\App\Http\Controllers\BankController::class, 'getBank']);
    Route::post('/bank/store', [\App\Http\Controllers\BankController::class, 'storeBank']);
    Route::post('/bank/update', [\App\Http\Controllers\BankController::class, 'updateBank']);
    Route::post('/bank/delete', [\App\Http\Controllers\BankController::class, 'deleteBank']);

    Route::get('/company', [\App\Http\Controllers\ApiController::class, 'getCompany']);
    Route::post('/company/update', [\App\Http\Controllers\ApiController::class, 'updateCompany']);
});
