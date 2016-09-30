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

$app->get('context/artifacts/get', 'ContextController@getArtifacts');
$app->get('context/children/get/{id}', 'ContextController@getChildren');
$app->get('context/get', 'ContextController@get');
$app->get('context/getAll', 'ContextController@getAll');
$app->get('context/getAttributes/{id}', 'ContextController@getAttributes');
$app->get('context/get/type/{id}', 'ContextController@getType');
$app->get('literature/getAll', 'LiteratureController@getAll');
$app->get('image/getAll', 'ImageController@getAll');
$app->get('image/get/{id}', 'ImageController@getImage');
$app->get('image/getByContext/{id}', 'ImageController@getByContext');
$app->get('gps/get/markers', 'GpsController@getMarkers');
$app->get('gps/get/markers/{id}', 'GpsController@getMarker');
$app->get('context/delete/{id}', 'ContextController@delete');
$app->post('image/upload', 'ImageController@uploadImage');
$app->post('context/add', 'ContextController@add');
$app->post('context/set', 'ContextController@set');
