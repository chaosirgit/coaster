<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


class RelationshipController extends Controller
{
	public $token;
	public $toUsername;
	public $toUid;
	public $istoken;
	public $fromUsername;
	public $fromUid;
	public $expand;

	public function __construct(Request $request){
		$this->token = $request->header('Authorization');
		$this->istoken = DB::select('select * from token where user_token=:token',['token'=>substr($this->token,7)]);
		$this->toUsername = $request->input('toUsername');
		$this->toUid = $request->input('toUid');
		$this->fromUsername = $request->input('fromUsername');
		$this->fromUid = $request->input('fromUid');
		$this->expand = $request->input('expand');
	}

	//发起关系绑定
	public function send_request(){
		if($this->istoken){
	 		$selfinfo = DB::select('select * from user where id=:id',['id'=>$this->istoken[0]->uid]);
			if(isset($this->toUsername))
			{
				$arr = DB::select('select * from relationship where to_username=:username or from_username=:rusername or to_username=:selfusername or from_username=:rselfusername',['username'=>$this->toUsername,'rusername'=>$this->toUsername,'selfusername'=>$selfinfo[0]->phone,'rselfusername'=>$selfinfo[0]->phone]);
				$info = DB::select('select * from user where phone=:phone',['phone'=>$this->toUsername]);
			}
			elseif(isset($this->toUid))
			{
				$arr = DB::select('select * from relationship where to_uid=:uid or from_uid=:ruid or to_uid=:selfuid or from_uid=:rselfuid',['uid'=>$this->toUid,'ruid'=>$this->toUid,'selfuid'=>$selfinfo[0]->id,'rselfuid'=>$selfinfo[0]->id]);
				$info = DB::select('select * from user where id=:id',['id'=>$this->toUid]);
			}
			elseif(isset($this->toUid) && isset($this->toUsername))
			{
				$arr = DB::select('select * from relationship where to_uid=:uid or from_uid=:ruid or to_uid=:selfuid or from_uid=:rselfuid',['uid'=>$this->toUid,'ruid'=>$this->toUid,'selfuid'=>$selfinfo[0]->id,'rselfuid'=>$selfinfo[0]->id]);
			$info = DB::select('select * from user where id=:id',['id'=>$this->toUid]);
			}
			else{
				return response('参数错误',400)->header('Content-Type','text/html;charset=utf-8');
				die;
			}
			if(!$info){return response('没有这个用户',500);die;}
			if($info[0]->id == $selfinfo[0]->id){return response('不能绑定自己',400);die;}
			if(!$arr){
				$insert['touid'] = $info[0]->id;
				$insert['status'] = '1';
				$insert['tousername'] = $info[0]->phone;
				$insert['starttime']=date('Y-m-d H:i:s',time()+28800);
				$insert['fromuid'] = $selfinfo[0]->id;
				$insert['fromusername'] = $selfinfo[0]->phone;
				
				$row = 	DB::insert('insert into relationship (to_uid,status,to_username,start_time,from_uid,from_username) values (:touid,:status,:tousername,:starttime,:fromuid,:fromusername)',$insert);	
				if($row){
					return response()->json(['fromUid'=>$selfinfo[0]->id,
						'toUid'=>$info[0]->id,
						'datetime'=>$insert['starttime'],
						'send'=>'1'],200);
				}else{
				return response('',500);
				}
			}
			if($arr[0]->status != 0){
			return response('已绑定/待绑定',409);
			}
		}else{
			return response('',401);
	 	}
	}
	
	//取消关系绑定
	public function cancel_request(){
		if($this->istoken){
			$selfinfo = DB::select('select * from user where id=:id',['id'=>$this->istoken[0]->uid]);
			if(isset($this->toUsername))
			{
				$arr = DB::select('select * from relationship where to_username=:username and status=1',['username'=>$this->toUsername]);
				$info = DB::select('select * from user where phone=:phone',['phone'=>$this->toUsername]);
			}
			elseif(isset($this->toUid))
			{
				$arr = DB::select('select * from relationship where to_uid=:uid and status=1',['uid'=>$this->toUid]);
				$info = DB::select('select * from user where id=:id',['id'=>$this->toUid]);
			}
			elseif(isset($this->toUid) && isset($this->toUsername))
			{
				$arr = DB::select('select * from relationship where to_uid=:uid and status=1',['uid'=>$this->toUid]);
			$info = DB::select('select * from user where id=:id',['id'=>$this->toUid]);
			}
			else{
				return response('参数错误',400)->header('Content-Type','text/html;charset=utf-8');
				die;
			}
			if(!$info){return response('没有这个用户',500);die;}
			if($info[0]->id == $selfinfo[0]->id){return response('不能取消自己',400);die;}
			if($arr){
				$row = 	DB::delete('delete from relationship where id=:id',['id'=>$arr[0]->id]);	
				if($row){
					return response('',200);
				}else{
				return response('',500);
				}
			}else{return response('没有待绑定信息',404);}
		}else{
			return response('',401);
		}
	}

	//接受关系绑定
	public function accept_request(){
			if($this->istoken){
			$selfinfo = DB::select('select * from user where id=:id',['id'=>$this->istoken[0]->uid]);
			if(isset($this->fromUsername))
			{
				$arr = DB::select('select * from relationship where from_username=:username and status=1',['username'=>$this->fromUsername]);
				$info = DB::select('select * from user where phone=:phone',['phone'=>$this->fromUsername]);
			}
			elseif(isset($this->fromUid))
			{
				$arr = DB::select('select * from relationship where from_uid=:uid and status=1',['uid'=>$this->fromUid]);
				$info = DB::select('select * from user where id=:id',['id'=>$this->fromUid]);
			}
			elseif(isset($this->fromUid) && isset($this->fromUsername))
			{
				$arr = DB::select('select * from relationship where from_uid=:uid and status=1',['uid'=>$this->fromUid]);
			$info = DB::select('select * from user where id=:id',['id'=>$this->fromUid]);
			}
			else{
				return response('参数错误',400)->header('Content-Type','text/html;charset=utf-8');
				die;
			}
			if(!$info){return response('没有这个用户',500);die;}
			if($info[0]->id == $selfinfo[0]->id){return response('不能接受自己',400);die;}
			if($arr){
				$row = 	DB::update('update relationship set status=2 where id=:id',['id'=>$arr[0]->id]);	
				if($row){
					return response()->json(['fromUid'=>$arr[0]->from_uid,
								'toUid'=>$arr[0]->to_uid,
								'dateTime'=>date('Y-m-d H:i:s',time()+28800),
								'Type'=>2],200);
				}else{
				return response('',500);
				}
			}else{return response('没有待绑定信息',404);}
		}else{
			return response('',401);
		}

	}

	//拒绝关系绑定
	public function deny_request(){
			if($this->istoken){
			$selfinfo = DB::select('select * from user where id=:id',['id'=>$this->istoken[0]->uid]);
			if(isset($this->fromUsername))
			{
				$arr = DB::select('select * from relationship where from_username=:username and status=1',['username'=>$this->fromUsername]);
				$info = DB::select('select * from user where phone=:phone',['phone'=>$this->fromUsername]);
			}
			elseif(isset($this->fromUid))
			{
				$arr = DB::select('select * from relationship where from_uid=:uid and status=1',['uid'=>$this->fromUid]);
				$info = DB::select('select * from user where id=:id',['id'=>$this->fromUid]);
			}
			elseif(isset($this->fromUid) && isset($this->fromUsername))
			{
				$arr = DB::select('select * from relationship where from_uid=:uid and status=1',['uid'=>$this->fromUid]);
			$info = DB::select('select * from user where id=:id',['id'=>$this->fromUid]);
			}
			else{
				return response('参数错误',400)->header('Content-Type','text/html;charset=utf-8');
				die;
			}
			if(!$info){return response('没有这个用户',500);die;}
			if($info[0]->id == $selfinfo[0]->id){return response('不能拒绝自己',400);die;}
			if($arr){
				$row = 	DB::delete('delete from relationship where id=:id',['id'=>$arr[0]->id]);	
				if($row){
					return response()->json(['fromUid'=>$arr[0]->from_uid,
								'toUid'=>$arr[0]->to_uid,
								'dateTime'=>date('Y-m-d H:i:s',time()+28800),
								'Type'=>3],200);
				}else{
				return response('',500);
				}
			}else{return response('没有待绑定信息',404);}
		}else{
			return response('',401);
		}

	}

	//获取关系
	public function get_relation(){
		if($this->istoken){
		$arr =	DB::select('select * from relationship where from_uid=:uid or to_uid=:ruid',['uid'=>$this->istoken[0]->uid,'ruid'=>$this->istoken[0]->uid]);	
		if(!$arr){
			return response('该用户没有任何绑定信息',404);
			die;
		}else{
			if($this->expand == true){
				return response()->json(['status'=>$arr[0]->status,
							'fromUid'=>$arr[0]->from_uid,
							'toUid'=>$arr[0]->to_uid,
							'startTime'=>$arr[0]->start_time,
							'fromUsername'=>$arr[0]->from_username,
							'toUsername'=>$arr[0]->to_username],200);
			}
				return response()->json(['status'=>$arr[0]->status,
							'fromUid'=>$arr[0]->from_uid,
							'toUid'=>$arr[0]->to_uid,
							'startTime'=>$arr[0]->start_time],200);
		}
			
		}else{
		return response('',401);
		}
	
	}

	//解除关系
	public function del_relation(){
	if($this->istoken){
	$arr =	DB::select('select * from relationship where from_uid=:uid or to_uid=:ruid',['uid'=>$this->istoken[0]->uid,'ruid'=>$this->istoken[0]->uid]);	
		if(!$arr){
			return response('该用户没有任何绑定信息',404);
			die;
		}else{
			$row = DB::delete('delete from relationship where id=:id',['id'=>$arr[0]->id]);

			if($row){
				return response()->json(['Type'=>4,
							'fromUid'=>$arr[0]->from_uid,
							'toUid'=>$arr[0]->to_uid,
							'startTime'=>$arr[0]->start_time],200);
			}else{return response('',500);}
		}

	}else{
	return response('',401);
	}	
	}
}
	 
