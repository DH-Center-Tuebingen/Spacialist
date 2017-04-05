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

$app->group(['middleware' => ['before' => 'jwt.auth', 'after' => 'jwt.refresh']], function($app) {
    $app->get('context/artifacts/get', 'ContextController@getArtifacts');
    $app->get('context/get/children/{id}', 'ContextController@getChildren');
    $app->get('context/get', 'ContextController@get');
    $app->get('context/get/attributes', 'ContextController@getAttributes');
    $app->get('context/get/attributes/types', 'ContextController@getAvailableAttributeTypes');
    $app->get('context/get/data/{id}', 'ContextController@getContextData');
    $app->get('context/get/geodata', 'ContextController@getGeodata');
    $app->get('context/get/byGeodata/{id}', 'ContextController@getContextByGeodata');
    $app->get('context/link/geodata/{cid}/{gid}', 'ContextController@linkGeodata');
    $app->get('context/unlink/geodata/{cid}', 'ContextController@unlinkGeodata');
    $app->get('context/get/parents/{id}', 'ContextController@getContextParents');
    $app->get('context/getRecursive', 'ContextController@getRecursive');
    $app->get('context/getChoices', 'ContextController@getChoices');
    $app->get('context/duplicate/{id}', 'ContextController@duplicate');
    $app->get('literature/getAll', 'LiteratureController@getAll');
    $app->get('literature/get/{id}', 'LiteratureController@getById');
    $app->get('literature/delete/{id}', 'LiteratureController@delete');
    $app->get('image/tags/get', 'ImageController@getAvailableTags');
    $app->get('image/getAll', 'ImageController@getAll');
    $app->get('image/get/info/{id}', 'ImageController@getImage');
    $app->get('image/get/{id}', 'ImageController@getImageObject');
    $app->get('image/get/preview/{id}', 'ImageController@getImagePreviewObject');
    $app->get('image/getByContext/{id}', 'ImageController@getByContext');
    $app->get('image/delete/{id}', 'ImageController@delete');
    $app->get('context/delete/{id}', 'ContextController@delete');
    $app->get('context/delete/geodata/{id}', 'ContextController@deleteGeodata');
    $app->get('sources/get/{aid}/{fid}', 'SourceController@getByAttribute');
    $app->get('sources/get/{id}', 'SourceController@getByContext');
    $app->get('sources/delete/{id}', 'SourceController@delete');
    $app->get('analysis/queries/getAll', 'AnalysisController@getAll');
    $app->get('user/delete/{id}', 'UserController@delete');
    $app->get('user/get/roles/all', 'UserController@getRoles');
    $app->get('user/get/roles/{id}', 'UserController@getRolesByUser');
    $app->get('user/get/role/permissions/{id}', 'UserController@getPermissionsByRole');
    $app->get('role/delete/{id}', 'UserController@deleteRole');
    $app->get('overlay/get/all', 'OverlayController@getAll');
    $app->get('editor/attribute/delete/{id}', 'ContextController@deleteAttribute');
    $app->get('editor/occurrences/{id}', 'ContextController@getOccurrenceCount');
    $app->get('editor/contexttype/delete/{id}', 'ContextController@deleteContextType');
    $app->post('image/upload', 'ImageController@uploadImage');
    $app->post('image/link', 'ImageController@link');
    $app->post('image/unlink', 'ImageController@unlink');
    $app->post('image/property/set', 'ImageController@setProperty');
    $app->post('image/tags/add', 'ImageController@addTag');
    $app->post('image/tags/remove', 'ImageController@removeTag');
    $app->post('context/add/geodata', 'ContextController@addGeodata');
    $app->post('context/set', 'ContextController@set');
    $app->post('context/set/color', 'ContextController@setColor');
    $app->post('sources/add', 'SourceController@add');
    $app->post('context/set/possibility', 'ContextController@setPossibility');
    $app->post('context/wktToGeojson', 'ContextController@wktToGeojson');
    $app->post('user/logout', 'UserController@logout');
    $app->post('user/switch', 'UserController@switchRole');
    $app->post('user/get', 'UserController@get');
    $app->post('user/get/all', 'UserController@getAll');
    $app->post('user/add', 'UserController@add');
    $app->post('user/edit', 'UserController@edit');
    $app->post('user/add/role', 'UserController@addRoleToUser');
    $app->post('user/remove/role', 'UserController@removeRoleFromUser');
    $app->post('role/edit', 'UserController@editRole');
    $app->post('role/add/permission', 'UserController@addRolePermission');
    $app->post('role/remove/permission', 'UserController@removeRolePermission');
    $app->post('literature/add', 'LiteratureController@add');
    $app->post('literature/edit', 'LiteratureController@edit');
    $app->post('editor/search', 'ContextController@search');
    $app->post('editor/contexttype/add', 'ContextController@addContextType');
    $app->post('editor/contexttype/edit', 'ContextController@editContextType');
    $app->post('editor/contexttype/attribute/add', 'ContextController@addAttributeToContextType');
    $app->post('editor/contexttype/attribute/remove', 'ContextController@removeAttributeFromContextType');
    $app->post('editor/contexttype/attribute/move/up', 'ContextController@moveAttributeUp');
    $app->post('editor/contexttype/attribute/move/down', 'ContextController@moveAttributeDown');
    $app->post('editor/attribute/add', 'ContextController@addAttribute');
});
