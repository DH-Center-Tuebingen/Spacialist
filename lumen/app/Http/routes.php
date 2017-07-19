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

$app->post('user/login', 'UserController@login');

$app->group([
        'prefix' => 'context',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
    $app->get('', 'ContextController@getContexts');
    $app->get('context_type/{id:[0-9]+}/attribute', 'ContextController@getContextTypeAttributes');
    $app->get('attribute', 'ContextController@getAttributes');
    $app->get('context_type', 'ContextController@getContextTypes');
    $app->get('{id:[0-9]+}/data', 'ContextController@getContextData');
    $app->get('dropdown_options', 'ContextController@getDropdownOptions');
    $app->get('byGeodata/{id:[0-9]+}', 'ContextController@getContextByGeodata');
    $app->get('attributetypes', 'ContextController@getAvailableAttributeTypes');
    $app->get('search/term={term}', 'ContextController@searchContextName');

    $app->post('', 'ContextController@add');
    $app->post('{id:[0-9]+}/duplicate', 'ContextController@duplicate');

    $app->patch('{id:[0-9]+}/rank', 'ContextController@patchRank');
    $app->patch('geodata/{cid:[0-9]+}', 'ContextController@linkGeodata');
    $app->patch('geodata/{cid:[0-9]+}/{gid:[0-9]+}', 'ContextController@linkGeodata');

    $app->put('{id:[0-9]+}', 'ContextController@put');
    $app->put('attribute_value/{cid:[0-9]+}/{aid:[0-9]+}', 'ContextController@putPossibility');

    $app->delete('{id:[0-9]+}', 'ContextController@delete');
});

$app->group([
        'prefix' => 'literature',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('', 'LiteratureController@getLiteratures');
        $app->get('{id:[0-9]+}', 'LiteratureController@getLiterature');

        $app->post('', 'LiteratureController@add');
        $app->post('importBibtex', 'LiteratureController@importBibtex');

        $app->patch('{id:[0-9]+}', 'LiteratureController@edit');

        $app->delete('{id:[0-9]+}', 'LiteratureController@delete');
});

$app->group([
        'prefix' => 'image',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('', 'ImageController@getImages');
        $app->get('{id:[0-9]+}', 'ImageController@getImage');
        $app->get('{id:[0-9]+}/object', 'ImageController@getImageObject');
        $app->get('tag', 'ImageController@getAvailableTags');
        $app->get('by_context/{id:[0-9]+}', 'ImageController@getByContext');

        $app->post('upload', 'ImageController@uploadImage');

        $app->patch('{id:[0-9]+}/property', 'ImageController@patchPhotoProperty');

        $app->put('link', 'ImageController@link');
        $app->put('tag', 'ImageController@addTag');

        $app->delete('{id:[0-9]+}', 'ImageController@delete');
        $app->delete('link/{pid:[0-9]+}/{cid:[0-9]+}', 'ImageController@unlink');
        $app->delete('{pid:[0-9]+}/tag/{tid:[0-9]+}', 'ImageController@removeTag');
});

$app->group([
        'prefix' => 'source',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('by_context/{cid:[0-9]+}', 'SourceController@getByContext');

        $app->post('', 'SourceController@add');

        $app->delete('{id:[0-9]+}', 'SourceController@delete');
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
        $app->get('permission', 'UserController@getPermissions');
        $app->get('role/by_user/{id:[0-9]+}', 'UserController@getRolesByUser');
        $app->get('role/{id:[0-9]+}/permission', 'UserController@getPermissionsByRole');

        $app->post('', 'UserController@add');
        $app->post('role', 'UserController@addRole');

        $app->patch('{id:[0-9]+}', 'UserController@patch');
        $app->patch('role/{name}', 'UserController@patchRole');
        $app->patch('{id:[0-9]+}/attachRole', 'UserController@addRoleToUser');
        $app->patch('{id:[0-9]+}/detachRole', 'UserController@removeRoleFromUser');

        $app->put('permission_role/{rid:[0-9]+}/{pid:[0-9]+}', 'UserController@putRolePermission');

        $app->delete('{id:[0-9]+}', 'UserController@delete');
        $app->delete('role/{id:[0-9]+}', 'UserController@deleteRole');
        $app->delete('permission_role/{rid:[0-9]+}/{pid:[0-9]+}', 'UserController@removeRolePermission');
});

$app->group([
        'prefix' => 'overlay',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('', 'OverlayController@getOverlays');
        $app->get('geometry_types', 'OverlayController@getGeometryTypes');

        $app->post('add', 'OverlayController@addLayer');

        $app->patch('{id:[0-9]+}/move/up', 'OverlayController@moveUp');
        $app->patch('{id:[0-9]+}/move/down', 'OverlayController@moveDown');
        $app->patch('{id:[0-9]+}', 'OverlayController@patchLayer');

        $app->delete('{id:[0-9]+}', 'OverlayController@deleteLayer');
});

$app->group([
        'prefix' => 'editor',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('occurrence_count/{id:[0-9]+}', 'ContextController@getOccurrenceCount');
        $app->get('search/label={label}', 'ContextController@searchForLabel');
        $app->get('search/label={label}/{lang}', 'ContextController@searchForLabel');

        $app->post('context_type', 'ContextController@addContextType');
        $app->post('context_type/{ctid:[0-9]+}/attribute', 'ContextController@addAttributeToContextType');
        $app->post('attribute', 'ContextController@addAttribute');

        $app->patch('context_type/{ctid:[0-9]+}', 'ContextController@editContextType');
        $app->patch('context_type/{ctid:[0-9]+}/attribute/{aid:[0-9]+}/move/up', 'ContextController@moveAttributeUp');
        $app->patch('context_type/{ctid:[0-9]+}/attribute/{aid:[0-9]+}/move/down', 'ContextController@moveAttributeDown');

        $app->delete('attribute/{id:[0-9]+}', 'ContextController@deleteAttribute');
        $app->delete('contexttype/{id:[0-9]+}', 'ContextController@deleteContextType');
        $app->delete('context_type/{ctid:[0-9]+}/attribute/{aid:[0-9]+}', 'ContextController@removeAttributeFromContextType');
});

$app->group([
        'prefix' => 'geodata',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('', 'GeodataController@get');
        $app->get('wktToGeojson/{wkt}', 'GeodataController@wktToGeojson');

        $app->post('', 'GeodataController@add');

        $app->put('{id:[0-9]+}', 'GeodataController@put');

        $app->delete('{id:[0-9]+}', 'GeodataController@delete');
});

$app->group([
    'prefix' => 'thesaurus',//TODO api v1
    'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
], function($app) {
    $app->get('concept/{lang}', 'ThesaurusController@getConcepts');
});
