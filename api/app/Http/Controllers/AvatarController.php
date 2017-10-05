<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
class AvatarController extends Controller
{
    public function post_image(Request $request)
    {
        if ($request->isMethod('post') && $request->is('api/avatar')){
            $token = $request->header('Authorization');
            $image = $request->input('image');
            $istoken = DB::select('select * from token where user_token=:token',['token'=>substr($token,7)]);
            if($istoken){
                $result = DB::select('select image from user where id=:id',['id'=>$istoken[0]->uid]);
                if($image == $result[0]->image)
                {
                    return response('',200)->header('Content-Type','text/html;charset=utf-8');
                }else {
                    $row = DB::update('update user set image=:image where id=:id', ['image' => $image, 'id' => $istoken[0]->uid]);
                    if ($row) {
                        return response('', 200)->header('Content-Type', 'text/html;charset=utf-8');
                    } else {
                        return response('', 500)->header('Content-Type', 'text/html;charset=utf-8');
                    }
                }
            }else{
                return response('',401)->header('Content-Type','text/html;charset=utf-8');
            }
        }else{
            return response('',500)->header('Content-Type','text/html;charset=utf-8');
        }
    }

    public function get_image(Request $request,$uid){
        if ($request->isMethod('get')){
            $arr = DB::select('select image from user where id=:id',['id'=>$uid]);
            if($arr){
                return base64_decode($arr[0]->image);
            }else{
                return response('',500)->header('Content-Type','text/html;charset=utf-8');
            }
        }else{
            return response('',500)->header('Content-Type','text/html;charset=utf-8');
        }
    }
}