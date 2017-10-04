<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


class DrinkController extends Controller
{
	public $token;
	public $datetime;
	public $weight;
	public $istoken;
	public $startdate;
	public $enddate;
	public $type;

	public function __construct(Request $request){
		$this->token = $request->header('Authorization');
		$this->datetime = $request->input('datetime');
		$this->istoken = DB::select('select * from token where user_token=:token',['token'=>substr($this->token,7)]);
		$this->startdate = $request->input('startDate').' 00:00:00';
		$this->enddate = $request->input('endDate').' 23:59:59';
		$this->type = $request->input('type');
		$this->weight = $request->input('weight');
	}

	public function post_drink(){

		if($this->istoken){
		$is = DB::select('select * from water where drink_date=:date and uid=:uid',['date'=>$this->datetime,'uid'=>$this->istoken[0]->uid]);
		if(isset($is[0]->id)){
			return response('',409)->header('Content-Type','text/html;charset=utf-8');
		}else{
			$row  =	DB::insert('insert into water (uid,drink_date,drink_water) values (:uid,:drinkdate,:drinkwater)',['uid'=>$this->istoken[0]->uid,'drinkdate'=>$this->datetime,'drinkwater'=>$this->weight]);
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


	public function get_drink($uuid = $this->istoken[0]->uid){
		if($this->istoken){
			if(!isset($this->startdate) || !isset($this->enddate) || $this->startdate>$this->enddate){
				return response('',400)->header('Content-Type','text/html;charset=utf-8');
			}
		$result  = DB::select('select * from water where (`drink_date` >= :startdate and `drink_date` <= :enddate) and `uid`=:uid;',['uid'=>$uuid,'startdate'=>$this->startdate,'enddate'=>$this->enddate]);
		if($result){
			if($this->type == 0){
				for($i=0;$i<count($result);$i++){
					$arr[$i]['datetime'] = date('Y-m-d',strtotime($result[$i]->drink_date));
					$arr[$i]['weight'] = $result[$i]->drink_water;
				}
				$aResult = array('Drinks'=>$arr);
				return response()->json($aResult,200);
			}else{
				for($i=0;$i<count($result);$i++){
					$arr[$i]['datetime'] = $result[$i]->drink_date;
					$arr[$i]['weight'] = $result[$i]->drink_water;
				}
				$aResult = array('Drinks'=>$arr);
				return response()->json($aResult,200);
			}
		}
		else{return response()->json(null,200);}		
	//var_dump($result);
		}else{
		return response('',401)->header('Content-Type','text/html;charset=utf-8');
		}
	}

	public function get_pair(){
        $result = DB::select('select * from relationship where from_uid=:fromid or to_uid=:toid',['fromid'=>$this->istoken[0]->uid,'toid'=>$this->istoken[0]->uid]);
        if(!$result)
        {
            return response('',404)->header('Content-Type','text/html;charset=utf-8');
        }
        if($result[0]->to_uid == $this->istoken[0]->uid)
        {
            $pairid = $result[0]->from_uid;
        }else{
            $pairid = $result[0]->to_uid;
        }
       return $this->get_drink($pairid);
    }

	public function post_health(){
		if($this->istoken){
			if(!isset($this->datetime)){
				return response('',400)->header('Content-Type','text/html;charset=utf-8');
			}
		$row = DB::insert('insert into water (uid,health) values (:uid,:health)',['uid'=>$this->istoken[0]->uid,'health'=>$this->datetime]);
		
			if($row){
			return response('',200)->header('Content-Type','text/html;charset=utf-8');
			}else{
			return response('',500)->header('Content-Type','text/html;charset=utf-8');
			}
		}else{
		return response('',401)->header('Content-Type','text/html;charset=utf-8');
		}
	}
}
