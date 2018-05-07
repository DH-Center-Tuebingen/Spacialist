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

// CONTEXT
Route::get('/context/{id}/data', 'ContextController@getData')->where('id', '[0-9]+');
Route::get('/context/{id}/children', 'ContextController@getChildren')->where('id', '[0-9]+');
Route::get('/context/{id}/reference', 'ReferenceController@getByContext')->where('id', '[0-9]+');
Route::get('/context/byParent/{id}', 'ContextController@getEntitiesByParent')->where('id', '[0-9]+');

Route::post('/context', 'ContextController@addEntity');
Route::post('/context/{id}/reference/{aid}', 'ReferenceController@addReference')->where('id', '[0-9]+')->where('aid', '[0-9]+');

Route::patch('/context/{id}/attributes', 'ContextController@patchAttributes')->where('id', '[0-9]+');
Route::patch('/context/{id}/attribute/{aid}', 'ContextController@patchAttribute')->where('id', '[0-9]+')->where('aid', '[0-9]+');
Route::patch('/reference/{id}', 'ReferenceController@patchReference')->where('id', '[0-9]+');

Route::delete('/context/{id}', 'ContextController@deleteContext')->where('id', '[0-9]+');
Route::delete('/reference/{id}', 'ReferenceController@delete')->where('id', '[0-9]+');

// SEARCH
Route::get('/search/context', 'SearchController@searchContextByName');
Route::get('/search/label', 'SearchController@searchInThesaurus');

// EDITOR
Route::get('/editor/dm/context_type/occurrence_count/{id}', 'EditorController@getContextTypeOccurrenceCount')->where('id', '[0-9]+');
Route::get('/editor/dm/attribute/occurrence_count/{aid}', 'EditorController@getAttributeOccurrenceCount')->where('aid', '[0-9]+');
Route::get('/editor/dm/attribute/occurrence_count/{aid}/{ctid}', 'EditorController@getAttributeOccurrenceCount')->where('aid', '[0-9]+')->where('ctid', '[0-9]+');
Route::get('/editor/dm/context_type/top', 'EditorController@getTopContextTypes');
Route::get('/editor/dm/context_type/parent/{cid}', 'EditorController@getContextTypesByParent')->where('cid', '[0-9]+');
Route::get('/editor/dm/attribute_types', 'EditorController@getAttributeTypes');
Route::get('/editor/context_type/{id}', 'EditorController@getContextType')->where('id', '[0-9]+');
Route::get('/editor/context_type/{id}/attribute', 'EditorController@getContextTypeAttributes')->where('id', '[0-9]+');
Route::get('/editor/attribute/{id}/selection', 'EditorController@getAttributeSelection')->where('id', '[0-9]+');
Route::get('/editor/dm/context_type/{ctid}/attribute/{aid}/dependency', 'EditorController@getDependency')->where('ctid', '[0-9]+')->where('aid', '[0-9]+');

Route::post('/editor/dm/context_type', 'EditorController@addContextType');
Route::post('/editor/dm/{id}/relation', 'EditorController@setRelationInfo')->where('id', '[0-9]+');
Route::post('/editor/dm/attribute', 'EditorController@addAttribute');
Route::post('/editor/dm/context_type/{ctid}/attribute', 'EditorController@addAttributeToContextType')->where('ctid', '[0-9]+');

Route::patch('/editor/dm/context_type/{ctid}/attribute/{aid}/position', 'EditorController@reorderAttribute')->where('ctid', '[0-9]+')->where('aid', '[0-9]+');
Route::patch('/editor/dm/context_type/{ctid}/attribute/{aid}/dependency', 'EditorController@patchDependency')->where('ctid', '[0-9]+')->where('aid', '[0-9]+');

Route::delete('/editor/dm/context_type/{id}', 'EditorController@deleteContextType')->where('id', '[0-9]+');
Route::delete('/editor/dm/attribute/{id}', 'EditorController@deleteAttribute')->where('id', '[0-9]+');
Route::delete('/editor/dm/context_type/{ctid}/attribute/{aid}', 'EditorController@removeAttributeFromContextType')->where('ctid', '[0-9]+')->where('aid', '[0-9]+');

// USER
Route::post('/user', 'UserController@addUser');
Route::post('/role', 'UserController@addRole');

Route::patch('/user/{id}/role', 'UserController@setRoles');
Route::patch('/role/{id}/permission', 'UserController@setPermissions');

Route::delete('/user/{id}', 'UserController@deleteUser')->where('id', '[0-9]+');
Route::delete('/role/{id}', 'UserController@deleteRole')->where('id', '[0-9]+');

// PREFERENCES
Route::patch('/preference/{id}', 'PreferenceController@patchPreference')->where('id', '[0-9]+');

// BIBLIOGRAPHY
Route::get('/bibliography/export', 'BibliographyController@exportBibtex');

Route::post('/bibliography', 'BibliographyController@addItem');
Route::post('/bibliography/import', 'BibliographyController@importBibtex');

// EXTENSIONS

// FILE
Route::get('/file', 'FileController@getFiles');
Route::get('/file/unlinked', 'FileController@getUnlinkedFiles');
Route::get('/file/linked/{cid}', 'FileController@getLinkedFiles')->where('cid', '[0-9]+');
Route::get('/file/{id}/archive/list', 'FileController@getArchiveFileList')->where('id', '[0-9]+');
Route::get('/file/{id}/archive/download', 'FileController@downloadArchivedFile')->where('id', '[0-9]+');
Route::get('/file/{id}/as_html', 'FileController@getAsHtml')->where('id', '[0-9]+');
Route::get('/file/{id}/link_count', 'FileController@getLinkCount')->where('id', '[0-9]+');

Route::post('/file', 'FileController@uploadFile');
// Should be patch, but file upload is not allowed as patch
Route::post('/file/{id}/patch', 'FileController@patchContent')->where('id', '[0-9]+');

Route::put('/file/{id}/link', 'FileController@linkToEntity')->where('id', '[0-9]+');

Route::delete('/file/{id}', 'FileController@deleteFile')->where('id', '[0-9]+');
Route::delete('/file/{fid}/link/{cid}', 'FileController@unlinkEntity')->where('fid', '[0-9]+')->where('cid', '[0-9]+');

// MAP
Route::get('/map', 'MapController@getData');

Route::post('/map', 'MapController@addGeometry');
Route::post('/map/layer', 'MapController@addLayer');

Route::patch('/map/{id}', 'MapController@updateGeometry')->where('id', '[0-9]+');
Route::patch('/map/layer/{id}', 'MapController@updateLayer')->where('id', '[0-9]+');

Route::delete('/map/{id}', 'MapController@delete')->where('id', '[0-9]+');

// ANALYSIS
Route::post('analysis/filter', 'AnalysisController@applyFilterQuery');
