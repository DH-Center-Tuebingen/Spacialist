<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// PLUGINS
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/plugin')->group(function() {
    Route::get('', 'PluginController@getPlugins');
    Route::get('/{id}', 'PluginController@installPlugin')->where('id', '[0-9]+');

    Route::post('', 'PluginController@uploadPlugin');

    Route::patch('/{id}', 'PluginController@updatePlugin')->where('id', '[0-9]+');

    Route::delete('/{id}', 'PluginController@uninstallPlugin')->where('id', '[0-9]+');
    Route::delete('/remove/{id}', 'PluginController@removePlugin')->where('id', '[0-9]+');
});

// ENTITY
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/entity')->group(function() {
    Route::get('/top', 'EntityController@getTopEntities');
    Route::get('/{id}', 'EntityController@getEntity')->where('id', '[0-9]+');
    Route::get('/entity_type/{etid}/data/{aid}', 'EntityController@getDataForEntityType')->where('etid', '[0-9]+')->where('aid', '[0-9]+');
    Route::get('/{id}/data', 'EntityController@getData')->where('id', '[0-9]+');
    Route::get('/{id}/data/{aid}', 'EntityController@getData')->where('id', '[0-9]+')->where('aid', '[0-9]+');
    Route::get('/{id}/reference', 'ReferenceController@getByEntity')->where('id', '[0-9]+');
    Route::get('/{id}/parentIds', 'EntityController@getParentIds')->where('id', '[0-9]+');
    Route::get('/byParent/{id}', 'EntityController@getEntitiesByParent')->where('id', '[0-9]+');

    Route::post('', 'EntityController@addEntity');
    Route::post('/{id}/duplicate', 'EntityController@duplicateEntity')->where('id', '[0-9]+');
    Route::post('/import', 'EntityController@importData')->where('id', '[0-9]+')->where('aid', '[0-9]+');
    Route::post('/import/validate', 'EntityController@validateImportData');
    Route::post('/{id}/reference/{aid}', 'ReferenceController@addReference')->where('id', '[0-9]+')->where('aid', '[0-9]+');
    Route::post('/moveMultiple', 'EntityController@moveEntities');

    Route::patch('/{id}/attributes', 'EntityController@patchAttributes')->where('id', '[0-9]+');
    Route::patch('/{id}/attribute/{aid}', 'EntityController@patchAttribute')->where('id', '[0-9]+')->where('aid', '[0-9]+');
    Route::patch('/multiedit', 'EntityController@multieditAttributes');
    Route::patch('/{id}/attribute/{aid}/moderate', 'EntityController@handleModeration')->where('id', '[0-9]+')->where('aid', '[0-9]+');
    Route::patch('/{id}/name', 'EntityController@patchName')->where('id', '[0-9]+');
    Route::patch('/{id}/rank', 'EntityController@moveEntity')->where('id', '[0-9]+');
    Route::patch('/reference/{id}', 'ReferenceController@patchReference')->where('id', '[0-9]+');

    Route::delete('/{id}', 'EntityController@deleteEntity')->where('id', '[0-9]+');
    Route::delete('/reference/{id}', 'ReferenceController@delete')->where('id', '[0-9]+');
});

// SEARCH
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/search')->group(function() {
    Route::get('', 'SearchController@searchGlobal');
    Route::get('/entity', 'SearchController@searchEntityByName');
    Route::get('/entity-type', 'SearchController@searchEntityTypes');
    Route::get('/label', 'SearchController@searchInThesaurus');
    Route::get('/attribute', 'SearchController@searchInAttributes');
    Route::get('/selection/{id}', 'SearchController@getConceptChildren')->where('id', '[0-9]+');
});

// EDITOR
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/editor')->group(function() {
    Route::get('/dm/entity_type/occurrence_count/{id}', 'EditorController@getEntityTypeOccurrenceCount')->where('id', '[0-9]+');
    Route::get('/dm/attribute/occurrence_count/{aid}', 'EditorController@getAttributeValueOccurrenceCount')->where('aid', '[0-9]+');
    Route::get('/dm/attribute/occurrence_count/{aid}/{ctid}', 'EditorController@getAttributeValueOccurrenceCount')->where('aid', '[0-9]+')->where('ctid', '[0-9]+');
    Route::get('/dm/entity_type/top', 'EditorController@getTopEntityTypes');
    Route::get('/dm/attribute', 'EditorController@getAttributes');
    Route::get('/dm/attribute_types', 'EditorController@getAttributeTypes');
    Route::get('/entity_type/{id}', 'EditorController@getEntityType')->where('id', '[0-9]+');
    Route::get('/dm/geometry', 'EditorController@getAvailableGeometryTypes');

    Route::post('/dm/entity_type', 'EditorController@addEntityType');
    Route::post('/dm/{id}/relation', 'EditorController@setRelationInfo')->where('id', '[0-9]+');
    Route::post('/dm/attribute', 'EditorController@addAttribute');
    Route::post('/dm/entity_type/{etid}/attribute', 'EditorController@addAttributeToEntityType')->where('etid', '[0-9]+');
    Route::post('/dm/entity_type/{ctid}/duplicate', 'EditorController@duplicateEntityType')->where('ctid', '[0-9]+');

    Route::patch('/dm/entity_type/{etid}', 'EditorController@patchEntityType')->where('etid', '[0-9]+');
    Route::patch('/dm/entity_type/{ctid}/attribute/{aid}/position', 'EditorController@reorderAttribute')->where('ctid', '[0-9]+')->where('aid', '[0-9]+');
    Route::patch('/dm/entity_type/{etid}/attribute/{aid}/dependency', 'EditorController@patchDependency')->where('etid', '[0-9]+')->where('aid', '[0-9]+');
    Route::patch('/dm/entity_type/attribute/system/{id}', 'EditorController@patchSystemAttribute')->where('id', '[0-9]+');

    Route::delete('/dm/entity_type/{id}', 'EditorController@deleteEntityType')->where('id', '[0-9]+');
    Route::delete('/dm/attribute/{id}', 'EditorController@deleteAttribute')->where('id', '[0-9]+');
    Route::delete('/dm/entity_type/attribute/{id}', 'EditorController@removeAttributeFromEntityType')->where('id', '[0-9]+');
});

// USER
Route::post('/v1/auth/login', 'UserController@login');

Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1')->group(function() {
    Route::get('/auth/refresh', 'UserController@refreshToken');
    Route::get('/auth/user', 'UserController@getUser');
    Route::get('/user', 'UserController@getUsers');
    Route::get('/role', 'UserController@getRoles');
    Route::get('/access/groups', 'UserController@getAccessGroups');

    Route::post('/user', 'UserController@addUser');
    Route::post('/user/avatar', 'UserController@addAvatar')->where('id', '[0-9]+');
    Route::post('/role', 'UserController@addRole');
    Route::post('/auth/logout', 'UserController@logout');

    Route::patch('/user/{id}', 'UserController@patchUser');
    Route::patch('/user/restore/{id}', 'UserController@restoreUser');
    Route::patch('/role/{id}', 'UserController@patchRole');
    Route::patch('/user/{id}/password/reset', 'UserController@resetPassword')->where('id', '[0-9]+');
    Route::patch('/user/{id}/password/confirm', 'UserController@confirmPassword')->where('id', '[0-9]+');

    Route::delete('/user/{id}', 'UserController@deleteUser')->where('id', '[0-9]+');
    Route::delete('/role/{id}', 'UserController@deleteRole')->where('id', '[0-9]+');
    Route::delete('/user/avatar', 'UserController@deleteAvatar')->where('id', '[0-9]+');
});

// COMMENTS
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/comment')->group(function () {
    Route::get('/resource/{id}', 'CommentController@getComments')->where('id', '[0-9]+');
    Route::get('/{id}/reply', 'CommentController@getCommentReplies')->where('id', '[0-9]+');

    Route::post('/', 'CommentController@addComment');

    Route::patch('/{id}', 'CommentController@patchComment')->where('id', '[0-9]+');

    Route::delete('/{id}', 'CommentController@deleteComment')->where('id', '[0-9]+');
});

// NOTIFICATIONS
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/notification')->group(function() {
    Route::patch('/read/{id}', 'NotificationController@markNotificationAsRead');
    Route::patch('/read', 'NotificationController@markAllNotificationsAsRead');

    Route::delete('/{id}', 'NotificationController@deleteNotification');
    // have to use patch, because delete does not support parameters
    Route::patch('/', 'NotificationController@deleteNotifications');
});

// PREFERENCES
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/preference')->group(function() {
    Route::get('', 'PreferenceController@getPreferences');
    Route::get('/{id}', 'PreferenceController@getUserPreferences')->where('id', '[0-9]+');

    Route::patch('/', 'PreferenceController@patchPreferences');
});

// BIBLIOGRAPHY
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/bibliography')->group(function() {
    Route::get('/', 'BibliographyController@getBibliography');
    Route::get('/{id}/ref_count', 'BibliographyController@getReferenceCount')->where('id', '[0-9]+');
    
    Route::post('/', 'BibliographyController@addItem');
    Route::post('/import', 'BibliographyController@importBibtex');
    Route::post('/export', 'BibliographyController@exportBibtex');
    
    // form data params are not recognized using patch, thus using post
    Route::post('/{id}', 'BibliographyController@updateItem')->where('id', '[0-9]+');

    Route::delete('/{id}', 'BibliographyController@deleteItem')->where('id', '[0-9]+');
    Route::delete('/{id}/file', 'BibliographyController@deleteItemFile')->where('id', '[0-9]+');
});

// ACTIVITY LOG
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/activity')->group(function() {
    Route::get('', 'ActivityController@getAll');
    Route::get('/{id}', 'ActivityController@getByUser')->where('id', '[0-9]+');

    Route::post('', 'ActivityController@getFiltered');
});

// TAGS
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/tag')->group(function() {
    Route::get('', 'TagController@getAll');
});

// EXTENSIONS

// FILE
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/file')->group(function() {
    Route::get('/{id}', 'FileController@getFile')->where('id', '[0-9]+');
    Route::get('/{id}/link_count', 'FileController@getLinkCount')->where('id', '[0-9]+');
    Route::get('/{id}/sub_files', 'FileController@getSubFiles')->where('id', '[0-9]+');

    Route::post('/unlinked', 'FileController@getUnlinkedFiles');
    Route::post('/linked/{cid}', 'FileController@getLinkedFiles')->where('cid', '[0-9]+');

    Route::delete('/{id}', 'FileController@deleteFile')->where('id', '[0-9]+');
});

// MAP
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/map')->group(function() {
    Route::post('epsg/text', 'MapController@getEpsgByText');

    Route::patch('/{id}', 'MapController@updateGeometry')->where('id', '[0-9]+');
    Route::patch('/layer/{id}/switch', 'MapController@changeLayerPositions')->where('id', '[0-9]+');
    Route::patch('/layer/{id}/move', 'MapController@moveLayer')->where('id', '[0-9]+');

    Route::delete('/{id}', 'MapController@delete')->where('id', '[0-9]+');
});

// ANALYSIS
Route::middleware(['before' => 'jwt.auth', 'after' => 'jwt.refresh'])->prefix('v1/analysis')->group(function() {
    Route::post('export', 'AnalysisController@export');
    Route::post('export/{type}', 'AnalysisController@export');
    Route::post('filter', 'AnalysisController@applyFilterQuery');
});

// Open Access
Route::prefix('v1/open')->group(function() {
    Route::get('global', 'OpenAccessController@getGlobals');
    Route::get('attributes', 'OpenAccessController@getAttributes');
    Route::get('types', 'OpenAccessController@getEntityTypes');

    Route::post('result', 'OpenAccessController@getFilterResults');
    Route::post('result/by_type/{id}', 'OpenAccessController@getFilterResultsForType')->where('id', '[0-9]+');
});