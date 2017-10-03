<?php

namespace App\Http\Controllers;

use App\Token;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class TokenController extends Controller
{
    public  $username;
    public  $password;

    public function __construct(Request $request){
	    
      $this->username = $request->input('userName');
      $this->password = $request->input('password');
    }
    public function get_token(Request $request)
    {
      //根据提交的查询
      $arr = DB::select('select * from user where phone=:username',['username'=>$this->username]);
      //如果没有注册
      if(!$arr){
	return response('',401)->header('Content-Type','text/html;charset=utf-8');
      }
      //注册过，对密码进行解密
      $depass = Crypt::decrypt($arr[0]->password);
      //如果提交的密码与解密后的密码一致
      if($this->password === $depass){
	//查询token表，判断是否获取过token
	$ishave = DB::select('select * from token where uid=:uid',['uid'=>$arr[0]->id]);    
	//如果获取过
	if($ishave){
	 // return response('',401)->header('Content-Type','text/html;charset=utf-8');
	  return response()->json(['access_token'=>$ishave[0]->user_token,
				   'token_type'=>'bearer',
				   'expires_in'=>'1209599',
				   'uid'=>strval($ishave[0]->uid),
				  'issued'=>gmdate(DATE_RFC822,$ishave[0]->issued_time+28800),
				   'expires'=>gmdate(DATE_RFC822,$ishave[0]->expire_time+28800)],200);
	}
	//否则
	else{
	  //生成token
	  $token = md5(md5(microtime(true).$this->username));
	  //插入uid,和token，其他两个字段在后边mysql有插入方法
	  DB::insert('insert into token (uid,user_token) values (:uid,:user_token)',['uid'=>$arr[0]->id,'user_token'=>$token]);
	  //准备返回的数据
	  $return_json = DB::select('select * from token where uid=:uid',['uid'=>$arr[0]->id]);
	  //时间差8小时，还原
	  $issued_time = strtotime($return_json[0]->issued_time)+28800;
	  $expire_time = strtotime($return_json[0]->expire_time)+28800;
	  //返回json
	  return response()->json(['access_token'=>$return_json[0]->user_token,
				   'token_type'=>'bearer',
				   'expires_in'=>'1209599',
				   'uid'=>strval($return_json[0]->uid),
				   'issued'=>gmdate(DATE_RFC822,$issued_time),
				   'expires'=>gmdate(DATE_RFC822,$expire_time)],200);

	}
	//如果密码不一致
      }else{
	return response('',401)->header('Content-Type','text/html;charset=utf-8');
    }
    }
}
?>
