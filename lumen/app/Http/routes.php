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

$app->post('test2', function(Request $request) {
	return response()->json($request);
});

$app->post('authtest', 'AuthController@postLogin');
$app->group(['middleware' => 'auth:api'], function($app) {
    $app->post('authtest2', 'AuthController@getAuthenticatedUser');
    $app->get('getauthtest', function() {
	return response()->json(['message' => 'Hello World!',]);
    });
});

$app->post('profile', function(Request $request, $id) {
	if(Auth::attempt($request)) return Auth::user();
	else return null;
});
