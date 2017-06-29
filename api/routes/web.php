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
$app->post('api/device','DeviceController@post_device');
$app->delete('api/device','DeviceController@delete_device');
$app->post('api/drink','DrinkController@post_drink');
$app->get('api/drink','DrinkController@get_drink');
$app->post('api/drink/suggest','SuggestController@post_suggest');
$app->get('api/drink/suggest','SuggestController@get_suggest');
$app->post('api/drinkhealths','DrinkController@post_health');
$app->get('api/drinkhealths/summary','SummaryController@issummary');
//$app->get('api/drinkhealths/summary?','SummaryController@get_summary');
