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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/version', function() {
    $versionInfo = new App\VersionInfo();
    return response()->json([
        'full' => $versionInfo->getFullRelease(),
        'readable' => $versionInfo->getReadableRelease(),
        'release' => $versionInfo->getRelease(),
        'name' => $versionInfo->getReleaseName(),
        'time' => $versionInfo->getTime()
    ]);
});

Route::get('/context/search', 'SearchController@searchContextByName');

Route::post('/user', 'UserController@addUser');
Route::delete('/user/{id}', 'UserController@deleteUser')->where('id', '[0-9]+');
Route::post('/role', 'UserController@addRole');
Route::delete('/role/{id}', 'UserController@deleteRole')->where('id', '[0-9]+');

Route::post('/bibliography/import', 'BibliographyController@importBibtex');
