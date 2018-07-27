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

Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1')->group(function() {
    Route::get('/pre', 'HomeController@getGlobalData');
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
});

// CONTEXT
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/context')->group(function() {
    Route::get('/top', 'ContextController@getTopEntities')->where('id', '[0-9]+');
    Route::get('/{id}', 'ContextController@getContext')->where('id', '[0-9]+');
    Route::get('/{id}/data', 'ContextController@getData')->where('id', '[0-9]+');
    Route::get('/{id}/children', 'ContextController@getChildren')->where('id', '[0-9]+');
    Route::get('/{id}/reference', 'ReferenceController@getByContext')->where('id', '[0-9]+');
    Route::get('/byParent/{id}', 'ContextController@getEntitiesByParent')->where('id', '[0-9]+');

    Route::post('', 'ContextController@addEntity');
    Route::post('/{id}/reference/{aid}', 'ReferenceController@addReference')->where('id', '[0-9]+')->where('aid', '[0-9]+');

    Route::patch('/{id}/attributes', 'ContextController@patchAttributes')->where('id', '[0-9]+');
    Route::patch('/{id}/attribute/{aid}', 'ContextController@patchAttribute')->where('id', '[0-9]+')->where('aid', '[0-9]+');
    Route::patch('/{id}/rank', 'ContextController@patchRank')->where('id', '[0-9]+');
    Route::patch('/reference/{id}', 'ReferenceController@patchReference')->where('id', '[0-9]+');

    Route::delete('/{id}', 'ContextController@deleteContext')->where('id', '[0-9]+');
    Route::delete('/reference/{id}', 'ReferenceController@delete')->where('id', '[0-9]+');
});

// SEARCH
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/search')->group(function() {
    Route::get('/context', 'SearchController@searchContextByName');
    Route::get('/label', 'SearchController@searchInThesaurus');
});

// EDITOR
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/editor')->group(function() {
    Route::get('/dm/context_type/occurrence_count/{id}', 'EditorController@getContextTypeOccurrenceCount')->where('id', '[0-9]+');
    Route::get('/dm/attribute/occurrence_count/{aid}', 'EditorController@getAttributeOccurrenceCount')->where('aid', '[0-9]+');
    Route::get('/dm/attribute/occurrence_count/{aid}/{ctid}', 'EditorController@getAttributeOccurrenceCount')->where('aid', '[0-9]+')->where('ctid', '[0-9]+');
    Route::get('/dm/context_type/top', 'EditorController@getTopContextTypes');
    Route::get('/dm/context_type/parent/{cid}', 'EditorController@getContextTypesByParent')->where('cid', '[0-9]+');
    Route::get('/dm/attribute', 'EditorController@getAttributes');
    Route::get('/dm/attribute_types', 'EditorController@getAttributeTypes');
    Route::get('/context_type/{id}', 'EditorController@getContextType')->where('id', '[0-9]+');
    Route::get('/context_type/{id}/attribute', 'EditorController@getContextTypeAttributes')->where('id', '[0-9]+');
    Route::get('/attribute/{id}/selection', 'EditorController@getAttributeSelection')->where('id', '[0-9]+');
    Route::get('/dm/context_type/{ctid}/attribute/{aid}/dependency', 'EditorController@getDependency')->where('ctid', '[0-9]+')->where('aid', '[0-9]+');
    Route::get('/dm/geometry', 'EditorController@getAvailableGeometryTypes');

    Route::post('/dm/context_type', 'EditorController@addContextType');
    Route::post('/dm/{id}/relation', 'EditorController@setRelationInfo')->where('id', '[0-9]+');
    Route::post('/dm/attribute', 'EditorController@addAttribute');
    Route::post('/dm/context_type/{ctid}/attribute', 'EditorController@addAttributeToContextType')->where('ctid', '[0-9]+');

    Route::patch('/dm/context_type/{ctid}/attribute/{aid}/position', 'EditorController@reorderAttribute')->where('ctid', '[0-9]+')->where('aid', '[0-9]+');
    Route::patch('/dm/context_type/{ctid}/attribute/{aid}/dependency', 'EditorController@patchDependency')->where('ctid', '[0-9]+')->where('aid', '[0-9]+');

    Route::delete('/dm/context_type/{id}', 'EditorController@deleteContextType')->where('id', '[0-9]+');
    Route::delete('/dm/attribute/{id}', 'EditorController@deleteAttribute')->where('id', '[0-9]+');
    Route::delete('/dm/context_type/{ctid}/attribute/{aid}', 'EditorController@removeAttributeFromContextType')->where('ctid', '[0-9]+')->where('aid', '[0-9]+');
});

// USER
Route::get('/v1/auth/refresh', 'UserController@refreshToken');
Route::post('/v1/auth/login', 'UserController@login');

Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1')->group(function() {
    Route::get('/auth/user', 'UserController@getUser');
    Route::get('/user', 'UserController@getUsers');
    Route::get('/role', 'UserController@getRoles');

    Route::post('/user', 'UserController@addUser');
    Route::post('/role', 'UserController@addRole');

    Route::patch('/user/{id}/role', 'UserController@setRoles');
    Route::patch('/role/{id}/permission', 'UserController@setPermissions');

    Route::delete('/user/{id}', 'UserController@deleteUser')->where('id', '[0-9]+');
    Route::delete('/role/{id}', 'UserController@deleteRole')->where('id', '[0-9]+');
});

// PREFERENCES
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/preference')->group(function() {
    Route::get('', 'PreferenceController@getPreferences');
    Route::get('/{id}', 'PreferenceController@getUserPreferences')->where('id', '[0-9]+');

    Route::patch('/{id}', 'PreferenceController@patchPreference')->where('id', '[0-9]+');
});

// BIBLIOGRAPHY
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/bibliography')->group(function() {
    Route::get('/', 'BibliographyController@getBibliography');
    Route::get('/export', 'BibliographyController@exportBibtex');
    Route::get('/{id}/ref_count', 'BibliographyController@getReferenceCount')->where('id', '[0-9]+');

    Route::post('/', 'BibliographyController@addItem');
    Route::post('/import', 'BibliographyController@importBibtex');

    Route::patch('/{id}', 'BibliographyController@updateItem')->where('id', '[0-9]+');

    Route::delete('/{id}', 'BibliographyController@deleteItem')->where('id', '[0-9]+');
});

// EXTENSIONS

// FILE
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/file')->group(function() {
    Route::get('/{id}', 'FileController@getFile')->where('id', '[0-9]+');
    Route::get('/{id}/archive/list', 'FileController@getArchiveFileList')->where('id', '[0-9]+');
    Route::get('/{id}/archive/download', 'FileController@downloadArchivedFile')->where('id', '[0-9]+');
    Route::get('/{id}/as_html', 'FileController@getAsHtml')->where('id', '[0-9]+');
    Route::get('/{id}/link_count', 'FileController@getLinkCount')->where('id', '[0-9]+');
    Route::get('/{id}/sub_files', 'FileController@getSubFiles')->where('id', '[0-9]+');
    // Filters
    Route::get('/filter/category', 'FileController@getCategories');
    Route::get('/filter/camera', 'FileController@getCameraNames');
    Route::get('/filter/date', 'FileController@getDates');


    Route::post('', 'FileController@getFiles');
    Route::post('/unlinked', 'FileController@getUnlinkedFiles');
    Route::post('/linked/{cid}', 'FileController@getLinkedFiles')->where('cid', '[0-9]+');
    Route::post('/new', 'FileController@uploadFile');
    // Should be patch, but file upload is not allowed as patch
    Route::post('/{id}/patch', 'FileController@patchContent')->where('id', '[0-9]+');

    Route::patch('/{id}/property', 'FileController@patchProperty')->where('id', '[0-9]+');

    Route::put('/{id}/link', 'FileController@linkToEntity')->where('id', '[0-9]+');

    Route::delete('/{id}', 'FileController@deleteFile')->where('id', '[0-9]+');
    Route::delete('/{fid}/link/{cid}', 'FileController@unlinkEntity')->where('fid', '[0-9]+')->where('cid', '[0-9]+');
});

// MAP
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/map')->group(function() {
    Route::get('', 'MapController@getData');
    Route::get('layer', 'MapController@getLayers');
    Route::get('layer/entity', 'MapController@getEntityTypeLayers');
    Route::get('layer/{id}', 'MapController@getLayer')->where('id', '[0-9]+');

    Route::post('', 'MapController@addGeometry');
    Route::post('/layer', 'MapController@addLayer');
    Route::post('/link/{gid}/{eid}', 'MapController@link')->where('gid', '[0-9]+')->where('eid', '[0-9]+');

    Route::patch('/{id}', 'MapController@updateGeometry')->where('id', '[0-9]+');
    Route::patch('/layer/{id}', 'MapController@updateLayer')->where('id', '[0-9]+');

    Route::delete('/{id}', 'MapController@delete')->where('id', '[0-9]+');
    Route::delete('/link/{gid}/{eid}', 'MapController@unlink')->where('gid', '[0-9]+')->where('eid', '[0-9]+');
});

// ANALYSIS
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/analysis')->group(function() {
    Route::post('export', 'AnalysisController@export');
    Route::post('export/{type}', 'AnalysisController@export');
    Route::post('filter', 'AnalysisController@applyFilterQuery');
});
