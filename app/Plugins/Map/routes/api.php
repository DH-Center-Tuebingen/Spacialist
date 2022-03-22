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

Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('')->group(function () {
    Route::get('', 'MapController@getData');
    Route::get('layer', 'MapController@getLayers');
    Route::get('layer/entity', 'MapController@getEntityTypeLayers');
    Route::get('layer/{id}', 'MapController@getLayer')->where('id', '[0-9]+');
    Route::get('layer/{id}/geometry', 'MapController@getGeometriesByLayer')->where('id', '[0-9]+');
    Route::get('epsg/{srid}', 'MapController@getEpsg')->where('srid', '[0-9]+');
    Route::get('export/{id}', 'MapController@exportLayer')->where('id', '[0-9]+');
    
    Route::post('', 'MapController@addGeometry');
    Route::post('/layer', 'MapController@addLayer');
    Route::post('/link', 'MapController@link');
    
    Route::patch('/layer/{id}', 'MapController@updateLayer')->where('id', '[0-9]+');

    Route::delete('layer/{id}', 'MapController@deleteLayer')->where('id', '[0-9]+');
    Route::delete('/link/{gid}/{eid}', 'MapController@unlink')->where('gid', '[0-9]+')->where('eid', '[0-9]+');
});
