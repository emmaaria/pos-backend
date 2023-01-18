<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Invoice API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'api'], function ($router) {
    Route::get('/invoice', [InvoiceController::class, 'getInvoices']);
    Route::get('/today-invoices', [InvoiceController::class, 'getTodayInvoices']);
    Route::get('/invoice/{id}', [InvoiceController::class, 'getInvoice']);
    Route::post('/invoice/store', [InvoiceController::class, 'storeInvoice']);
    Route::post('/invoice/update', [InvoiceController::class, 'updateInvoice']);
    Route::post('/invoice/delete', [InvoiceController::class, 'deleteInvoice']);
});
