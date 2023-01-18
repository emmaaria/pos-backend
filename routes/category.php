<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Category API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/category', [CategoryController::class, 'getCategories']);
    Route::get('/category/{id}', [CategoryController::class, 'getCategory']);
    Route::post('/category/store', [CategoryController::class, 'storeCategory']);
    Route::post('/category/update', [CategoryController::class, 'updateCategory']);
    Route::post('/category/delete', [CategoryController::class, 'deleteCategory']);
});
