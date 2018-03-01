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

// SEARCH
Route::get('/search/context', 'SearchController@searchContextByName');
Route::get('/search/label', 'SearchController@searchInThesaurus');

// EDITOR
Route::get('/editor/dm/context_type/occurrence_count/{id}', 'EditorController@getContextTypeOccurrenceCount')->where('id', '[0-9]+');
Route::get('/editor/dm/attribute/occurrence_count/{aid}', 'EditorController@getAttributeOccurrenceCount')->where('aid', '[0-9]+');
Route::get('/editor/dm/attribute/occurrence_count/{aid}/{ctid}', 'EditorController@getAttributeOccurrenceCount')->where('aid', '[0-9]+')->where('ctid', '[0-9]+');
Route::get('/editor/dm/attribute_types', 'EditorController@getAttributeTypes');
Route::get('/editor/context_type/{id}/attribute', 'EditorController@getContextTypeAttributes')->where('id', '[0-9]+');

Route::post('/editor/dm/context_type', 'EditorController@addContextType');
Route::post('/editor/dm/attribute', 'EditorController@addAttribute');
Route::post('/editor/dm/context_type/{ctid}/attribute', 'EditorController@addAttributeToContextType')->where('ctid', '[0-9]+');

Route::patch('/editor/dm/context_type/{ctid}/attribute/{aid}/position', 'EditorController@reorderAttribute')->where('ctid', '[0-9]+')->where('aid', '[0-9]+');

Route::delete('/editor/dm/context_type/{id}', 'EditorController@deleteContextType')->where('id', '[0-9]+');
Route::delete('/editor/dm/attribute/{id}', 'EditorController@deleteAttribute')->where('id', '[0-9]+');
Route::delete('/editor/dm/context_type/{ctid}/attribute/{aid}', 'EditorController@removeAttributeFromContextType')->where('ctid', '[0-9]+')->where('aid', '[0-9]+');

// USER
Route::post('/user', 'UserController@addUser');
Route::post('/role', 'UserController@addRole');

Route::patch('/user/{id}/role', 'UserController@setRoles');

Route::delete('/user/{id}', 'UserController@deleteUser')->where('id', '[0-9]+');
Route::delete('/role/{id}', 'UserController@deleteRole')->where('id', '[0-9]+');

// PREFERENCES
Route::patch('/preference/{id}', 'PreferenceController@patchPreference')->where('id', '[0-9]+');

// BIBLIOGRAPHY
Route::get('/bibliography/export', 'BibliographyController@exportBibtex');

Route::post('/bibliography', 'BibliographyController@addItem');
Route::post('/bibliography/import', 'BibliographyController@importBibtex');
