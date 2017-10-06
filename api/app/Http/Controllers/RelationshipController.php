<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use JPush\Client as JPush;


class RelationshipController extends Controller
{
	public $token;
	public $toUsername;
	public $toUid;
	public $istoken;
	public $fromUsername;
	public $fromUid;
	public $expand;
	public $app_key       = '7fa7f247619f0156b1761172';
	public $master_secret = 'ec2bf690679f2118837db197';

	public function __construct(Request $request){
		$this->token = $request->header('Authorization');
		$this->istoken = DB::select('select * from token where user_token=:token',['token'=>substr($this->token,7)]);
		$this->toUsername = $request->input('toUserName') ?? null;
		$this->toUid = $request->input('toUid') ?? null;
		$this->fromUsername = $request->input('fromUsername') ?? null;
		$this->fromUid = $request->input('fromUid') ?? null;
		$this->expand = $request->input('expand') ?? null;
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
                    //推送
                    $push_content = array(
                        'fromUid'=>$selfinfo[0]->id,
                        'toUid' => $info[0]->id,
                        'dateTime' => date('Y-m-d H:i:s',time()+28800),
                        'type' => '1'
                    );
                    $alias = str_replace('-','',$info[0]->id);
                    $client = new JPush($this->app_key,$this->master_secret);
                    $resp = $client->push()
                        ->addAlias($alias)
                        ->setPlatform('all')
                        ->iosNotification($selfinfo[0]->nickname.'邀请您作为 TA 的伴侣',['sound'=>'sound','extras'=>$push_content])
                        //->$push->iosNotification('hello', [
                       //     'sound' => 'sound',
                        ->message($selfinfo[0]->nickname.'邀请您作为 TA 的伴侣',['extras'=>$push_content]);
                        $resp->send();
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
			}else{
			    return response('没有待绑定信息',404);}
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
                    //推送
                    $push_content = array(
                        'fromUid'=>$arr[0]->from_uid,
                        'toUid' => $arr[0]->to_uid,
                        'dateTime' => date('Y-m-d H:i:s',time()+28800),
                        'type' => '2'
                    );
                    $to_nickname = DB::select('select nickname from user where id=:id',['id'=>$arr[0]->to_uid]);
                    $alias = str_replace('-','',$arr[0]->from_uid);
                    $client = new JPush($this->app_key,$this->master_secret);
                    $resp = $client->push()
                        ->addAlias($alias)
                        ->setPlatform('all')
                        ->iosNotification($to_nickname[0]->nickname.'接受了您的邀请',['sound'=>'sound','extras'=>$push_content])
                        ->message($to_nickname[0]->nickname.'接受了您的邀请',['extras'=>$push_content]);
                    $resp->send();
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
                    //推送
                    $push_content = array(
                        'fromUid'=>$arr[0]->from_uid,
                        'toUid' => $arr[0]->to_uid,
                        'dateTime' => date('Y-m-d H:i:s',time()+28800),
                        'type' => '3'
                    );
                    $alias = str_replace('-','',$arr[0]->from_uid);
                    $client = new JPush($this->app_key,$this->master_secret);
                    $resp = $client->push()
                        ->addAlias($alias)
                        ->setPlatform('all')
                        ->iosNotification($info[0]->nickname.'拒绝了您的邀请',['sound'=>'sound','extras'=>$push_content])
                        ->message($info[0]->nickname.'拒绝了您的邀请',['extras'=>$push_content]);
                    $resp->send();
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
			    $fromNickname = DB::select('select nickname from user where id = :fromid',['fromid'=>$arr[0]->from_uid]);
			    $toNickname = DB::select('select nickname from user where id = :toid',['toid'=>$arr[0]->to_uid]);
				return response()->json(['status'=>$arr[0]->status,
							'fromUid'=>$arr[0]->from_uid,
							'toUid'=>$arr[0]->to_uid,
							'startTime'=>$arr[0]->start_time,
							'fromNickname'=>$fromNickname[0]->nickname,
							'toNickname'=>$toNickname[0]->nickname],200);
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
            $from_uid = $this->istoken[0]->uid;
            if($from_uid == $arr[0]->from_uid)
            {
                $to_uid = $arr[0]->to_uid;
            }else{
                $to_uid = $arr[0]->from_uid;
            }
            $fromNickname = DB::select('select nickname from user where id = :fromid',['fromid'=>$from_uid]);
//            $toNickname = DB::select('select nickname from user where id = :toid',['toid'=>$to_uid]);

            $row = DB::delete('delete from relationship where id=:id',['id'=>$arr[0]->id]);

			if($row){
                //推送
                $push_content = array(
                    'fromUid'=>$from_uid,
                    'toUid' => $to_uid,
                    'dateTime' => date('Y-m-d H:i:s',time()+28800),
                    'type' => '4'
                );
                $alias = str_replace('-','',$to_uid);
                $client = new JPush($this->app_key,$this->master_secret);
                $resp = $client->push()
                    ->addAlias($alias)
                    ->setPlatform('all')
                    ->iosNotification($fromNickname[0]->nickname.'解除了与您的伴侣绑定',['sound'=>'sound','extras'=>$push_content])
                    ->message($fromNickname[0]->nickname.'解除了与您的伴侣绑定',['extras'=>$push_content]);
                    $resp->send();
				return response()->json(['Type'=>4,
							'fromUid'=>$from_uid,
							'toUid'=>$to_uid,
							'startTime'=>$arr[0]->start_time],200);

			}else{return response('',500);}
		}

	}else{
	return response('',401);
	}	
	}

	public function notification(Request $request){
	    $content = $request->input('notification') ?? response(null,400);
        if($this->istoken){
            $arr =	DB::select('select * from relationship where from_uid=:uid or to_uid=:ruid',['uid'=>$this->istoken[0]->uid,'ruid'=>$this->istoken[0]->uid]);
            if(!$arr){
                return response('该用户没有任何绑定信息',400);
                die;
            }else{
                $from_uid = $this->istoken[0]->uid;
                if($from_uid == $arr[0]->from_uid)
                {
                    $to_uid = $arr[0]->to_uid;
                }else{
                    $to_uid = $arr[0]->from_uid;
                }
                $row = DB::insert('insert into text (from_uid, to_uid, content, created_at) values (:from_uid,:to_uid,:content,:create_at)',['from_uid'=>$from_uid,'to_uid'=>$to_uid,'content'=>$content,'create_at'=>date('Y-m-d H:i:s',time()+28800]));
                if($row){
                    //推送
                    $push_content = array(
                        'fromUid'=>$from_uid,
                        'toUid' => $to_uid,
                        'dateTime' => date('Y-m-d H:i:s',time()+28800),
                        'type' => '5'
                    );
                    $alias = str_replace('-','',$to_uid);
                    $client = new JPush($this->app_key,$this->master_secret);
                    $resp = $client->push()
                        ->addAlias($alias)
                        ->setPlatform('all')
                        ->iosNotification($content,['sound'=>'sound','extras'=>$push_content])
                        ->message($content,['extras'=>$push_content]);
                    $resp->send();
                    return response()->json(null,200);
                }else{return response(null,500);}
            }
        }else{return response(null,401);
        }
    }

    public function feedback(Request $request){
	    $content = $request->input('content') ?? response(null,400);
        if($this->istoken){
            $row = DB::insert('insert into text (from_uid, to_uid, content, created_at) values (:from_uid,:to_uid,:content,:create_at)',['from_uid'=>$this->istoken[0]->uid,'to_uid'=>'0','content'=>$content,'create_at'=>time()+28800]);
            if($row){
                return response(null,200);
            }else{return response(null,500);}
        }else{return response(null,401);}
    }
}
	 
