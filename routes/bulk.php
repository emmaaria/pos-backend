<?php

use App\Http\Controllers\BulkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Bank API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/product-bulk-data', [BulkController::class, 'productBulkData']);
});
