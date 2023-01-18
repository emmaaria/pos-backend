<?php

use Illuminate\Http\Request;
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
require_once('invoice.php');
Route::group(['middleware' => 'api'], function ($router) {
    Route::post('login', [\App\Http\Controllers\ApiController::class, 'login']);
    Route::get('/profile', [\App\Http\Controllers\ApiController::class, 'profile']);

    Route::get('/stock', [\App\Http\Controllers\ApiController::class, 'getStock']);

    Route::get('/company', [\App\Http\Controllers\ApiController::class, 'getCompany']);
    Route::post('/company/update', [\App\Http\Controllers\ApiController::class, 'updateCompany']);
});
