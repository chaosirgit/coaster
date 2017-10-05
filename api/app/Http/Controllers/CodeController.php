<?php

namespace App\Http\Controllers;
header("Content-type:text/html; charset=UTF-8");

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;//引入数据库操作门面


class CodeController extends Controller
{
    public $url = 'http://106.ihuyi.com/webservice/sms.php?method=Submit';
    public $apiid = 'C93100065';
    public $apikey = 'a74575afed73a39cbd417b2754eac43d';
    public $mobile;
    public $content;
    public $format = 'json';

    public function send($mobile)
    {
        $iscode = strval($this->random());
//        var_dump($iscode);
        $this->mobile = strval($mobile);
//        var_dump($this->mobile);
        $this->content = '您的验证码是：'.$iscode.'。请不要把验证码泄露给其他人。';
        $postvalue = array(
            'account'=>$this->apiid,
            'password'=>$this->apikey,
            'mobile'=>$this->mobile,
            'content'=>$this->content,
            'format'=>$this->format
        );

        $row = DB::select('select * from session where phone=:phone',['phone'=>$this->mobile]);
//        var_dump($row);
        if($row){
            $result = DB::update('update session set code=:code where phone=:phone',['code'=>$iscode,'phone'=>$this->mobile]);
//            var_dump($result);
        }else{
            $result = DB::insert('insert into session (code,phone) values (:code,:phone)',['code'=>$iscode,'phone'=>$this->mobile]);
//            dd($result);die;
        }
//        dd($result);die;
        return $this->Post($postvalue);
    }


//请求数据到短信接口，检查环境是否 开启 curl init。
    public function Post($postvalue)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postvalue);
        $return_str = curl_exec($curl);
        curl_close($curl);
        return $return_str;
    }

    //random() 函数返回随机整数。
    public function random($length = 6)
    {
        $row = rand(100000,999999);
            return $row;
    }

    public function code(Request $request)
    {
        $phone = $request->input('phone');
        $type = $request->input('type') ?? null;
        $return_str = $this->send($phone);
        $oReturn = json_decode($return_str);
        if($oReturn->code == 2){
            return response()->json('',200);
        }else{
            return response()->json('',400);
        }
    }
}