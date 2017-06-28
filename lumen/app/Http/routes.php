<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\User;
use Illuminate\Http\Request;

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->post('user/login', 'UserController@login');//TODO
$app->group([
        'prefix' => 'context',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
    $app->get('', 'ContextController@getContexts');
    $app->get('artifact', 'ContextController@getArtifacts');
    $app->get('context_type', 'ContextController@getContextTypes');
    $app->get('attribute', 'ContextController@getAttributes');
    $app->get('{id}/data', 'ContextController@getContextData');
    $app->get('dropdown_options', 'ContextController@getDropdownOptions');
    $app->get('byGeodata/{id}', 'ContextController@getContextByGeodata');

    $app->post('', 'ContextController@add');
    $app->post('{id}/duplicate', 'ContextController@duplicate');

    $app->patch('{id}/rank', 'ContextController@patchRank');
    $app->patch('geodata/{cid}/{gid?}', 'ContextController@linkGeodata');

    $app->put('{id}', 'ContextController@put');
    $app->put('geodata/{id}', 'ContextController@putGeodata');
    $app->put('attribute_value/{cid}/{aid}', 'ContextController@putPossibility');

    $app->delete('{id}', 'ContextController@delete');

    $app->get('attributetypes', 'ContextController@getAvailableAttributeTypes');
});

$app->group([
        'prefix' => 'literature',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('', 'LiteratureController@getLiteratures');
        $app->get('{id}', 'LiteratureController@getLiterature');

        $app->post('', 'LiteratureController@add');

        $app->patch('{id}', 'LiteratureController@edit');

        $app->delete('{id}', 'LiteratureController@delete');

        $app->put('importBibtex', 'LiteratureController@importBibtex');
});

$app->group([
        'prefix' => 'image',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('', 'ImageController@getImages');
        $app->get('{id:[0-9]+}', 'ImageController@getImage');
        $app->get('{id:[0-9]+}/object', 'ImageController@getImageObject');
        $app->get('tag', 'ImageController@getAvailableTags');
        $app->get('by_context/{id}', 'ImageController@getByContext');

        $app->post('upload', 'ImageController@uploadImage');

        $app->patch('{id}/property', 'ImageController@patchPhotoProperty');

        $app->put('link', 'ImageController@link');
        $app->put('tag', 'ImageController@addTag');

        $app->delete('{id}', 'ImageController@delete');
        $app->delete('link/{pid}/{cid}', 'ImageController@unlink');
        $app->delete('{pid}/tag/{tid}', 'ImageController@removeTag');
});

$app->group([
        'prefix' => 'source',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('by_context/{cid}', 'SourceController@getByContext');

        $app->post('', 'SourceController@add');

        $app->delete('{id}', 'SourceController@delete');

});

$app->group([
        'prefix' => 'analysis',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('', 'AnalysisController@getAnalyses');
});

$app->group([
        'prefix' => 'user',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('', 'UserController@getUsers');
        $app->get('active', 'UserController@getActiveUser');
        $app->get('role', 'UserController@getRoles');
        $app->get('role/by_user/{id}', 'UserController@getRolesByUser');
        $app->get('role/{id}/permission', 'UserController@getPermissionsByRole');

        $app->post('', 'UserController@add');

        $app->patch('{id}/', 'UserController@patch');
        $app->patch('role/{name}', 'UserController@patchRole');
        $app->patch('{id}/attachRole', 'UserController@addRoleToUser');
        $app->patch('{id}/detachRole', 'UserController@removeRoleFromUser');

        $app->put('role/{name}', 'UserController@putRole');
        $app->put('permission_role/{rid}/{pid}', 'UserController@putRolePermission');

        $app->delete('{id}', 'UserController@delete');
        $app->delete('role/{id}', 'UserController@deleteRole');
        $app->delete('permission_role/{rid}/{pid}', 'UserController@removeRolePermission');
});

$app->group([
        'prefix' => 'overlay',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('', 'OverlayController@getOverlays');
});

$app->group([
        'prefix' => 'editor',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('occurrence_count/{id}', 'ContextController@getOccurrenceCount');
        $app->get('search/label={label}/{lang?}', 'ContextController@searchForLabel');


        $app->post('context_type', 'ContextController@addContextType');
        $app->post('context_type/{ctid}/attribute', 'ContextController@addAttributeToContextType');
        $app->post('attribute', 'ContextController@addAttribute');

        $app->patch('context_type/{ctid}', 'ContextController@editContextType');
        $app->patch('context_type/{ctid}/attribute/{aid}/move/up', 'ContextController@moveAttributeUp');
        $app->patch('context_type/{ctid}/attribute/{aid}/move/down', 'ContextController@moveAttributeDown');

        $app->delete('attribute/{id}', 'ContextController@deleteAttribute');
        $app->delete('contexttype/{id}', 'ContextController@deleteContextType');
        $app->delete('editor/context_type/{ctid}/attribute/{aid}', 'ContextController@removeAttributeFromContextType');
});

$app->group([
        'prefix' => 'geodata',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('', 'GeodataController@get');
        $app->get('wktToGeojson/{wkt}', 'GeodataController@wktToGeojson');

        $app->post('', 'GeodataController@add');

        $app->put('{id}', 'GeodataController@put');

        $app->delete('{id}', 'GeodataController@delete');
});
