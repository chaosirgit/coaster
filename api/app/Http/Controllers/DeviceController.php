<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


class DeviceController extends Controller
{
	public $token;
	public $deviceId;
	public $istoken;

	public function __construct(Request $request){
		$this->token = $request->header('Authorization');
		$this->deviceId = $request->input('deviceId');
		$this->istoken = DB::select('select * from token where user_token=:token',['token'=>substr($this->token,7)]);
	}

	public function post_device(){

		if($this->istoken){
		$isdevice = DB::select('select id,deviceid from user where id=:id',['id'=>$this->istoken[0]->uid]);
		if(isset($isdevice[0]->deviceid)){
			return response('该用户已绑定过设备',409)->header('Content-Type','text/html;charset=utf-8');
		}else{
			$row  =	DB::update('update user set deviceid=:deviceid where id=:id',['deviceid'=>$this->deviceId,'id'=>$this->istoken[0]->uid]);
			if($row){
			return response('',200)->header('Content-Type','text/html;charset=utf-8');
			}else{
			return response('其他错误',500)->header('Content-Type','text/html;charset=utf-8');
			}
		}
		}else{
		return response('',401)->header('Content-Type','text/html;charset=utf-8');
		}
	
	}


	public function delete_device(){
		if($this->istoken){
		$seledev = DB::select('select id,deviceid from user where id=:id',['id'=>$this->istoken[0]->uid]);
		if(!isset($seledev[0]->deviceid)){
		return response('该用户目前没有绑定设备',404)->header('Content-Type','text/html;charset=utf-8');
		}else{
		  $row = DB::update('update user set deviceid=null where id=:id',['id'=>$this->istoken[0]->uid]);
			if($row){
			return response('',200)->header('Content-Type','text/html;charset=utf-8');
			}else{
			return response('',500)->header('Content-Type','text/html;charset=utf-8');
			}
		}	
	
		}else{
		return response('',401)->header('Content-Type','text/html;charset=utf-8');
		}
	}
}
