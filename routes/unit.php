<?php

use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Unit API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/unit', [UnitController::class, 'getUnits']);
    Route::get('/unit/{id}', [UnitController::class, 'getUnit']);
    Route::post('/unit/store', [UnitController::class, 'storeUnit']);
    Route::post('/unit/update', [UnitController::class, 'updateUnit']);
    Route::post('/unit/delete', [UnitController::class, 'deleteUnit']);
});
