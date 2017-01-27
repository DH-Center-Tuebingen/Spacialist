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
    $app->get('context/getRecursive', 'ContextController@getRecursive');
    $app->get('context/getChoices', 'ContextController@getChoices');
    $app->get('context/duplicate/{id}', 'ContextController@duplicate');
    $app->get('literature/getAll', 'LiteratureController@getAll');
    $app->get('image/getAll', 'ImageController@getAll');
    $app->get('image/get/info/{id}', 'ImageController@getImage');
    $app->get('image/get/{id}', 'ImageController@getImageObject');
    $app->get('image/get/preview/{id}', 'ImageController@getImagePreviewObject');
    $app->get('image/getByContext/{id}', 'ImageController@getByContext');
    $app->get('context/delete/{id}', 'ContextController@delete');
    $app->get('sources/get/{aid}/{fid}', 'SourceController@getByAttribute');
    $app->get('sources/get/{id}', 'SourceController@getByContext');
    $app->get('sources/delete/literature/{aid}/{fid}/{lid}', 'SourceController@deleteByLiterature');
    $app->get('analysis/queries/getAll', 'AnalysisController@getAll');
    $app->post('image/upload', 'ImageController@uploadImage');
    $app->post('image/link', 'ImageController@link');
    $app->post('image/unlink', 'ImageController@unlink');
    $app->post('context/add', 'ContextController@add');
    $app->post('context/set', 'ContextController@set');
    $app->post('context/set/icon', 'ContextController@setIcon');
    $app->post('sources/add', 'SourceController@add');
    $app->post('context/set/possibility', 'ContextController@setPossibility');
    $app->get('user/delete/{id}', 'UserController@delete');
    $app->post('user/logout', 'UserController@logout');
    $app->post('user/switch', 'UserController@switchRole');
    $app->post('user/get', 'UserController@get');
    $app->post('user/get/all', 'UserController@getAll');
    $app->post('user/add', 'UserController@add');
    $app->get('user/get/roles/all', 'UserController@getRoles');
    $app->get('user/get/roles/{id}', 'UserController@getRolesByUser');
    $app->post('user/add/role', 'UserController@addRoleToUser');
    $app->post('user/remove/role', 'UserController@removeRoleFromUser');
});
