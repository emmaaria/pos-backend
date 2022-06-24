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
    Route::get('/category/:id', [\App\Http\Controllers\ApiController::class, 'getCategory']);
    Route::post('/category/store', [\App\Http\Controllers\ApiController::class, 'storeCategory']);
    Route::post('/category/delete', [\App\Http\Controllers\ApiController::class, 'deleteCategory']);
});
