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

Route::get('/', function () {
    return view('welcome');
});

Route::get('install', '\App\Http\Controllers\InstallController@install');
Route::get('auth', '\App\Http\Controllers\InstallController@auth')->name('auth');
Route::post('payments', '\App\Http\Controllers\PaymentController@handle')->name('payments');
Route::post('refunds', '\App\Http\Controllers\RefundController@handle')->name('refunds');
