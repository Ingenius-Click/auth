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

// Route::get('example', function () {
//     return 'Hello from package web route!';
// });

Route::prefix('auth')->name('auth.')->group(function () {
    // Add your web routes here
});
