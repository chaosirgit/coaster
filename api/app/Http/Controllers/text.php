<?php
$nickname = '赵国庆df';
$str = preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9]{1,20}$/u',$nickname);
var_dump($str);
