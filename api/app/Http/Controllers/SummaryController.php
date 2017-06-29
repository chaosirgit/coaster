<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


class SummaryController extends Controller
{
	public $token;
//	public $datetime;
//	public $weight;
	public $istoken;
	public $startdate;
	public $enddate;
//	public $type;

	public function __construct(Request $request){
		$this->token = $request->header('Authorization');
//		$this->datetime = $request->input('datetime');
		$this->istoken = DB::select('select * from token where user_token=:token',['token'=>substr($this->token,7)]);
		$this->startdate = $request->input('startdate');
		$this->enddate = $request->input('enddate');
//		$this->type = $request->input('type');
	}

	public function issummary(){
		if(isset($this->startdate) && isset($this->enddate)){
		return	$this->get_summary();
		}
		else{
		return	$this->get_beat();}
	}

	public function get_summary(){

		if($this->istoken){
		$result = DB::select('select * from water where (health>=:startdate and health<=:enddate) and uid=:uid',['startdate'=>$this->startdate,'enddate'=>$this->enddate,'uid'=>$this->istoken[0]->uid]);
		if(!isset($result[0]->id)){
		return response('无数据',500)->header('Content-Type','text/html;charset=utf-8');
		}
		return response()->json(['uid'=>$result[0]->uid,
					'datetime'=>$this->startdate.'至'.$this->enddate,
					'healthdays'=>count($result)],200);
		}else{
		return response('',401)->header('Content-Type','text/html;charset=utf-8');
		}
	}

	public function get_beat(){
	return response()->json(['score'=>10,'rank'=>100],200);
	}
}
