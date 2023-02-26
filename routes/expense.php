<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Category API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/expense/category', [ExpenseController::class, 'categoryIndex']);
    Route::get('/expense/category/{id}', [ExpenseController::class, 'singleCategory']);
    Route::post('/expense/category/store', [ExpenseController::class, 'storeCategory']);
    Route::post('/expense/category/update', [ExpenseController::class, 'updateCategory']);
    Route::post('/expense/category/delete', [ExpenseController::class, 'deleteCategory']);

    Route::get('/expense', [ExpenseController::class, 'index']);
    Route::post('/expense/store', [ExpenseController::class, 'store']);
});
