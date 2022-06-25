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
});
