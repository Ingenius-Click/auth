<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    // Add your API routes here
});

// Route::get('example', function () {
//     return response()->json(['message' => 'Hello from package API route!']);
// });