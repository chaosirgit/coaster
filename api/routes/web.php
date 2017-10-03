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

$app->post('api/account/register','UserController@register');//注册用户
$app->post('/token','TokenController@get_token');//登陆
$app->put('api/account/profile','ProfileController@update');//更改用户资料
$app->get('api/account/profile','ProfileController@getinfo');//获取用户资料
$app->post('api/avatar','AvatarController@post_image');//上传用户头像
$app->get('api/avatar/{uid}','AvatarController@get_image');//获取用户头像
$app->post('api/device','DeviceController@post_device');//绑定设备
$app->delete('api/device','DeviceController@delete_device');//解绑设备
$app->post('api/drink','DrinkController@post_drink');//添加饮水记录
$app->get('api/drink','DrinkController@get_drink');//查看当前用户饮水记录
$app->post('api/drink/suggest','SuggestController@post_suggest');//上传用户建议饮水量
$app->get('api/drink/suggest','SuggestController@get_suggest');//获取用户建议饮水量
$app->post('api/drinkhealths','DrinkController@post_health');//提交用户饮水健康
$app->get('api/drinkhealths/summary','SummaryController@issummary');//查看用户饮水健康统计
$app->post('api/relationship/sendrequest','RelationshipController@send_request');//发起关系绑定请求
$app->post('api/relationship/cancelrequest','RelationshipController@cancel_request');//取消关系绑定请求
$app->post('api/relationship/denyrequest','RelationshipController@deny_request');//拒绝关系绑定请求
$app->post('api/relationship/acceptrequest','RelationshipController@accept_request');//接受关系绑定请求
$app->get('api/relationship','RelationshipController@get_relation');//获取当前用户关系状态
$app->delete('api/relationship','RelationshipController@del_relation');//解除当前用户关系状态
