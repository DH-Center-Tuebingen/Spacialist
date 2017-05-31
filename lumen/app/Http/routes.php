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
    $app->get('attribute', 'ContextController@getAttributes'); //TODO probably wrong controller/group
    $app->get('{id}/data', 'ContextController@getContextData');
    $app->get('dropdown_options', 'ContextController@getDropdownOptions');

    $app->post('{id}/duplicate', 'ContextController@duplicate');

    $app->delete('{id}', 'ContextController@delete');
});

$app->group([
        'prefix' => 'literature',//TODO api v1
        'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
    ], function($app) {
        $app->get('', 'LiteratureController@getLiteratures');
        $app->get('{id}', 'LiteratureController@getLiterature');

        $app->delete('{id}', 'LiteratureController@delete');
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

        $app->post('', 'UserController@add');//TODO: should be put as soon as email is unique

        $app->patch('{id}/', 'UserController@patch');
        $app->patch('{id}/attachRole', 'UserController@addRoleToUser');
        $app->patch('{id}/detachRole', 'UserController@removeRoleFromUser');

        $app->delete('{id}', 'UserController@delete');
        $app->delete('role/{id}', 'UserController@deleteRole');


        //TODO
        $app->post('role/edit', 'UserController@editRole');
        $app->post('role/add/permission', 'UserController@addRolePermission');
        $app->post('role/remove/permission', 'UserController@removeRolePermission');
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
        $app->get('occurrence_count/{id}', 'ContextController@getOccurrenceCount'); //TODO: own Controller?
        $app->get('search/label={label}/{lang?}', 'ContextController@searchForLabel'); //TODO: own Controller?


        $app->post('context_type', 'ContextController@addContextType'); //TODO: own Controller?
        $app->post('context_type/{ctid}/attribute', 'ContextController@addAttributeToContextType'); //TODO: own Controller?
        $app->post('attribute', 'ContextController@addAttribute'); //TODO: own Controller?

        $app->patch('context_type/{ctid}', 'ContextController@editContextType'); //TODO: own Controller?
        $app->patch('context_type/{ctid}/attribute/{aid}/move/up', 'ContextController@moveAttributeUp'); //TODO: own Controller?
        $app->patch('context_type/{ctid}/attribute/{aid}/move/down', 'ContextController@moveAttributeDown'); //TODO: own Controller?

        $app->delete('attribute/{id}', 'ContextController@deleteAttribute'); //TODO: own Controller?
        $app->delete('contexttype/{id}', 'ContextController@deleteContextType'); //TODO: own Controller?
        $app->delete('editor/context_type/{ctid}/attribute/{aid}', 'ContextController@removeAttributeFromContextType'); //TODO: own Controller?
});

// $app->group([
//         'prefix' => '',//TODO api v1
//         'middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']
//     ], function($app) {
//
// });

$app->group(['middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']], function($app) {
    $app->get('context/get/geodata', 'ContextController@getGeodata');//TODO own Controller for Geodata?
    $app->get('context/get/byGeodata/{id}', 'ContextController@getContextByGeodata');//TODO own Controller for Geodata?
    $app->get('context/link/geodata/{cid}/{gid}', 'ContextController@linkGeodata');//TODO own Controller for Geodata?
    $app->get('context/unlink/geodata/{cid}', 'ContextController@unlinkGeodata');//TODO own Controller for Geodata?
    $app->get('context/delete/geodata/{id}', 'ContextController@deleteGeodata');//TODO own Controller for Geodata?
    $app->get('get/attributes/types', 'ContextController@getAvailableAttributeTypes'); //TODO correct Controller?
    $app->post('context/add/geodata', 'ContextController@addGeodata');
    $app->post('context/set', 'ContextController@set');
    $app->post('context/set/props', 'ContextController@setProperties');
    $app->post('context/move', 'ContextController@move');
    $app->post('context/set/possibility', 'ContextController@setPossibility');
    $app->post('context/wktToGeojson', 'ContextController@wktToGeojson');
    $app->post('sources/add', 'SourceController@add');
    $app->post('literature/add', 'LiteratureController@add');
    $app->post('literature/edit', 'LiteratureController@edit');
    $app->post('literature/import/bib', 'LiteratureController@importBibtex');
});
