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
Route::get('/label/search', 'SearchController@searchInThesaurus');

Route::get('/editor/dm/occurrence_count/{id}', 'EditorController@getOccurrenceCount')->where('id', '[0-9]+');
Route::post('/editor/dm/context_type', 'EditorController@addContextType');
Route::delete('/editor/dm/context_type/{id}', 'EditorController@deleteContextType')->where('id', '[0-9]+');

Route::post('/user', 'UserController@addUser');
Route::post('/role', 'UserController@addRole');
Route::delete('/user/{id}', 'UserController@deleteUser')->where('id', '[0-9]+');
Route::delete('/role/{id}', 'UserController@deleteRole')->where('id', '[0-9]+');

Route::patch('/preference/{id}', 'PreferenceController@patchPreference')->where('id', '[0-9]+');

Route::get('/bibliography/export', 'BibliographyController@exportBibtex');
Route::post('/bibliography', 'BibliographyController@addItem');
Route::post('/bibliography/import', 'BibliographyController@importBibtex');
