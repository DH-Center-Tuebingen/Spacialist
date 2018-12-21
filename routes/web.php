<?php

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

Route::get('/', 'HomeController@welcome')->name('welcome');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/preferences', 'HomeController@prefs')->name('prefs');
Route::get('/preferences/u/{id}', 'HomeController@userPrefs')->name('userprefs')->where('id', '[0-9]+');

Route::get('/editor/layer', 'HomeController@layer')->name('layer');

Route::get('/tool/gis', 'HomeController@gis')->name('gis');
Route::get('/tool/analysis', 'HomeController@analysis')->name('analysis');
