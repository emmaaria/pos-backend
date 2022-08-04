<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/migrate', function () {
    $output = [];
    \Artisan::call('migrate', $output);
    dd('Done');
});
Route::get('/clear', function () {
    $output = [];
    \Artisan::call('optimize:clear', $output);
    dd('Done');
});
Route::get('/', function () {
    return view('welcome');
});
Route::get('/jwt', [\App\Http\Controllers\ApiController::class, 'jwt_dec']);
//Auth::routes();

//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
