<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Bank API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/bank', [\App\Http\Controllers\BankController::class, 'getBanks']);
    Route::get('/bank/{id}', [\App\Http\Controllers\BankController::class, 'getBank']);
    Route::post('/bank/store', [\App\Http\Controllers\BankController::class, 'storeBank']);
    Route::post('/bank/update', [\App\Http\Controllers\BankController::class, 'updateBank']);
    Route::post('/bank/delete', [\App\Http\Controllers\BankController::class, 'deleteBank']);
});
