<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


class SuggestController extends Controller
{
	public $token;
	public $datetime;
	public $weight;
	public $istoken;
	public $startDate;
	public $endDate;
//	public $type;

	public function __construct(Request $request){
		$this->token = $request->header('Authorization');
		$this->datetime = $request->input('datetime');
		$this->istoken = DB::select('select * from token where user_token=:token',['token'=>substr($this->token,7)]);
		$this->startDate = $request->input('startDate');
		$this->enddate = $request->input('endDate');
//		$this->type = $request->input('type');
		$this->weight = $request->input('weight');
	}

	public function post_suggest(){

		if($this->istoken){
		$is = DB::select('select * from water where suggest_date=:date and uid=:uid',['date'=>$this->datetime,'uid'=>$this->istoken[0]->uid]);
		if(isset($is[0]->id)){
			return response('该时间已记录',409)->header('Content-Type','text/html;charset=utf-8');
		}else{
			$row  =	DB::insert('insert into water (uid,suggest_date,suggest_water) values (:uid,:suggestdate,:suggestwater)',['uid'=>$this->istoken[0]->uid,'suggestdate'=>$this->datetime,'suggestwater'=>$this->weight]);
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


	public function get_suggest(){
		if($this->istoken){
			if(!isset($this->startDate) && !isset($this->endDate)){
				return response('',400)->header('Content-Type','text/html;charset=utf-8');
			}
		$result  = DB::select('select * from water where `suggest_date`>:startdate and `suggest_date`<:enddate and `uid` = :uid',['uid'=>$this->istoken[0]->uid,'startdate'=>$this->startDate,'enddate'=>$this->enddate]);
		if(!$result){return response('',404)->header('Content-Type','text/html;charset=utf-8');}	
			//	if($this->type == 0){
			//	for($i=0;$i<count($result);$i++){
			//		$arr[$i]['datetime'] = date('Y-m-d',strtotime($result[$i]->drink_date));
			//		$arr[$i]['weight'] = $result[$i]->drink_water;
			//	}
		//	var_dump($arr);
		//		return response()->json($arr,200);	
		//	}else{
				for($i=0;$i<count($result);$i++){
					$arr[$i]['datetime'] = $result[$i]->suggest_date;
					$arr[$i]['weight'] = $result[$i]->suggest_water;
				}
				return response()->json($arr,200);
					
	
		}else{
		return response('',401)->header('Content-Type','text/html;charset=utf-8');
		}
	}
}
