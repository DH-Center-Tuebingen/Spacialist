<?php

use Illuminate\Http\Request;

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

// Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v2/file')->group(function () {
    Route::get('/', 'FileController@get');
// });