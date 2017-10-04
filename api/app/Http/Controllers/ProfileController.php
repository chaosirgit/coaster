<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{

	public function update(Request $request)
	{
		//获取数据
		$token	=  $request->header('Authorization');
		$expand =  $request->input('expand');
		$nickname = $request->input('nickname');
		$gender = $request->input('gender');
		$birthday = $request->input('birthday');
		$height = $request->input('height');
		$weight = $request->input('weight');
		$email = $request->input('email');
		//如果token正确
		$istoken = DB::select('select * from token where user_token=:token',['token'=>substr($token,7)]);
		if($istoken){
			//验证字符串
			if(isset($nickname) && !preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{1,20}$/u',$nickname)){
			return response('昵称不能超过20个字符且不能含有符号',400)->header('Content-Type','text/html;charset=utf-8');die;}
			if(isset($birthday) && !preg_match('/\d{4}-\d{2}-\d{2}/',$birthday)){
			return response('请填写正确的生日格式',400)->header('Content-Type','text/html;charset=utf-8');die;}
			if(isset($height) && !preg_match('/^\d{2,3}$/',$height)){
			return response('请填写正确的身高',400)->header('Content-Type','text/html;charset=utf-8');die;}
			if(isset($weight) && !preg_match('/^\d{2,3}$/',$weight)){
			return response('请填写正确的体重',400)->header('Content-Type','text/html;charset=utf-8');die;}
			if(isset($email) && !preg_match('/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/',$email)){
			return response('请填写正确的电子邮箱',400)->header('Content-Type','text/html;charset=utf-8');die;}
			//构建sql语句
			$sql = 'update user set ';
			if(isset($nickname)){$sql_columns[] = 'nickname=:nickname';$update_values['nickname']=$nickname;}
			if(isset($gender)){$sql_columns[] = 'gender=:gender';$update_values['gender']=$gender;}
			if(isset($birthday)){$sql_columns[] = 'birthday=:birthday';$update_values['birthday']=$birthday;}
			if(isset($height)){$sql_columns[] = 'height=:height';$update_values['height']=$height;}
			if(isset($weight)){$sql_columns[] = 'weight=:weight';$update_values['weight']=$weight;}
			if(isset($email)){$sql_columns[] = 'email=:email';$update_values['email']=$email;}
			$update_values['id']=$istoken[0]->uid;
			$sql .= implode(',',$sql_columns);
			$sql .= ' where id=:id';
			//更改资料
			$row =	DB::update($sql,$update_values);
			//如果更改成功
			if($row){
				//设置最新更改资料的时间
				DB::update('update user set updated_at=now() where id=:id',['id'=>$update_values['id']]);
				//准备返回的数据
				$info =	DB::select('select * from user where id=:id',['id'=>$update_values['id']]);
				//构建返回项
				$return_values['uid']=$info[0]->id;
				$return_values['email']=$info[0]->email;
				$return_values['nickname']=$info[0]->nickname;
				$return_values['gender']=$info[0]->gender;
				$return_values['birthday']=$info[0]->birthday;
				$return_values['height']=$info[0]->height;
				$return_values['weight']=$info[0]->weight;
				$return_values['phone']=$info[0]->phone;
				if($expand==true){$return_values['deviceid']=$info[0]->deviceid;}
					//返回json格式数据
					return response()->json($return_values,200);		
				//更改失败或无更改	
				}else{
					return response('',500)->header('Content-Type','text/html;charset=utf-8');
					}
		}				
	//token不正确
		else{
			return response('',401)->header('Content-Type','text/html;charset=utf-8');
		}
	}

	public function getinfo(Request $request)
	{
		$token = $request->header('Authorization');
		$username = $request->input('username');
		$uid = $request->input('uid');
		$expand = $request->input('expand');
		//验证token
		$istoken = DB::select('select * from token where user_token=:token',['token'=>substr($token,7)]);
		if($istoken){
			//构建sql语句
			$sql = 'select * from user where ';
			//按指定参数查询
			if(isset($uid)){$sql_column = 'id=:id';$select_values['id']=$uid;}
			elseif(isset($username)){$sql_column = 'phone=:phone';$select_values['phone']=$username;}
			//两者都存在按uid查询
			elseif(isset($uid) && isset($username)){$sql_column = 'id=:id';$select_values['id']=$uid;}
			//不指定返回当前用户信息
			else{
			$sql_column = 'id=:id';$select_values['id']=$istoken[0]->uid;
			}
			$sql .= $sql_column;
			$info = DB::select($sql,$select_values);
			//用户不存在返回404
			if(!$info){
				return response('用户不存在',404)->header('Content-Type','text/html;charset=utf-8');
			}	
			//构建返回项
				$return_values['uid']=strval($info[0]->id);
				$return_values['email']=$info[0]->email;
				$return_values['nickname']=$info[0]->nickname;
				$return_values['gender']=$info[0]->gender;
				$return_values['birthday']=$info[0]->birthday;
				$return_values['height']=$info[0]->height;
				$return_values['weight']=$info[0]->weight;
				$return_values['phone']=$info[0]->phone;
				if($expand==true){$return_values['deviceid']=$info[0]->deviceid;}
					//返回json格式数据
					return response()->json($return_values,200);		
		//token验证失败
		}else{
		return response('',401)->header('Content-Type','text/html;charset=utf-8');
		}
	}
}
