<?php

use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::controller(AffiliateController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::post('/invite', 'invite');
});
