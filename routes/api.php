<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
require_once('bank.php');
require_once('category.php');
require_once('unit.php');
require_once('customer.php');
require_once('supplier.php');
require_once('purchase.php');
require_once('product.php');
require_once('invoice.php');
require_once('sale-return.php');
Route::group(['middleware' => 'api'], function ($router) {
    Route::post('login', [ApiController::class, 'login']);
    Route::get('/profile', [ApiController::class, 'profile']);

    Route::get('/stock', [ApiController::class, 'getStock']);

    Route::get('/company', [ApiController::class, 'getCompany']);
    Route::post('/company/update', [ApiController::class, 'updateCompany']);
});
