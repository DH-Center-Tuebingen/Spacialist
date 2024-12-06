<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

Route::get('/welcome', 'HomeController@welcome')->name('welcome');
Route::get('/open', 'HomeController@external')->name('external');

Auth::routes(["middleware" => ["auth:sanctum"]]);

Route::get('/', 'HomeController@index')->name('home');
