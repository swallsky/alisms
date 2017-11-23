<?php
/**
 * 测试短信发送
 */
require('../vendor/autoload.php');
use SwaSky\Alisms\Sms;

$res = Sms::setVenderPath(__DIR__)->send('手机号码',[
    'cname'     =>  '公司名称',
    'password'  =>  mt_rand(100,999)
],'SMS_112780100');

if($res==0){
    echo '发送失败!';
}else{
    echo '发送成功!';
}