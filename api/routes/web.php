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

$app->post('api/account/register','UserController@register');
$app->post('/token','TokenController@get_token');
$app->put('api/account/profile','ProfileController@update');
$app->get('api/account/profile','ProfileController@getinfo');
$app->post('api/avatar','AvatarController@post_image');
$app->get('api/avatar/{uid}','AvatarController@get_image');
