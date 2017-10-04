<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response; //这个会自动引用
use Illuminate\Support\Facades\Crypt;//加密所需门面
use Illuminate\Support\Facades\DB;//引入数据库操作门面

class UserController extends Controller
{
	public function register (Request $request){
	$phone = $request->input('phone');
	$password = $request->input('password');
	$code= $request->input('code');
	$gender= $request->input('gender');
	$nickname= $request->input('nickname') ?? null;
	$birthday= $request->input('birthday') ?? null;
	$height= $request->input('height') ?? null;
	$weight= $request->input('weight') ?? null;
	$email= $request->input('email') ?? null;

	//如果验证码正确	
	if($code == '888888'){
		if(!preg_match('/^\d{11}$/',$phone)){
		//return 'phone flase <br>';}
		return response('',400)->header('Content-Type','text/html;charset=utf-8');}
		if(!preg_match('/^[0-9a-zA-Z\S]{6,16}$/',$password)){
		//return 'password is 6-16 <br>';}
		return response('',400)->header('Content-Type','text/html;charset=utf-8');}
		if($gender == null){
		//return 'select gender <br>';}
		return response('',400)->header('Content-Type','text/html;charset=utf-8');}
		if($nickname != null && !preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{1,20}$/u',$nickname)){
	//	return 'nickname mast <=20 <br>';}	
		return response('',400)->header('Content-Type','text/html;charset=utf-8');}
		if($birthday != null && !preg_match('/\d{4}-\d{2}-\d{2}/',$birthday)){
		//return 'birthday: yyyy-mm-dd <br>';}
		return response('',400)->header('Content-Type','text/html;charset=utf-8');}
		if($height != null && !preg_match('/^\d{2,3}$/',$height)){
		//return 'height is 2-3bit number <br>';}
		return response('',400)->header('Content-Type','text/html;charset=utf-8');}
		if($weight != null && !preg_match('/^\d{2,3}$/',$weight)){
		//return 'weight is 2-3bit number <br>';}
		return response('',400)->header('Content-Type','text/html;charset=utf-8');}
		if($email != null && !preg_match('/\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14}/',$email)){
		return response('',400)->header('Content-Type','text/html;charset=utf-8');}
		//是否存在重复的
		$userlist = DB::select('select id,phone,email from user where phone = :phone or email = :email',['phone' => $phone,'email' => $email]);
		if($userlist){
			return response('',400)->header('Content-Type','text/html;charset=utf-8');
		}else{
			if($gender == 'true'){$gender = 1;}
			if($gender == 'false'){$gender = 0;}
			//生成UUID
                $str = md5(uniqid(time(), true));
                $uuid  = substr($str,0,8) . '-';
                $uuid .= substr($str,8,4) . '-';
                $uuid .= substr($str,12,4) . '-';
                $uuid .= substr($str,16,4) . '-';
                $uuid .= substr($str,20,12);

		DB::insert('insert into user (id,created_at,phone,password,gender,nickname,birthday,height,weight,email) values (:id,:created_at,:phone,:password,:gender,:nickname,:birthday,:height,:weight,:email)',['id'=>$uuid,'created_at'=>date('Y-m-d H:i:s'),'phone'=>$phone,'password'=>Crypt::encrypt($password),'gender'=>$gender,'nickname'=>$nickname,'birthday'=>$birthday,'height'=>$height,'weight'=>$weight,'email'=>$email]);
		return response('',200)->header('Content-Type','text/html;charset=utf-8');
	//    	return 'oK';	
		}
		
	}else{
	//	return '验证码错误';
		return response('',400)->header('Content-Type','text/html;charset=utf-8');
	}
	}
}
?>
