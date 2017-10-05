<?php
namespace App\Sms;


class Sms
{
public $url = 'http://106.ihuyi.cn/webservice/sms.php?method=Submit';
public $apiid = 'C93100065';
public $apikey = 'a74575afed73a39cbd417b2754eac43d';
public $mobile;
public $content;
public $format = 'json';


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
    public function random($length = 6 , $numeric = 0) {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if($numeric) {
            $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
            $max = strlen($chars) - 1;
            for($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }
}