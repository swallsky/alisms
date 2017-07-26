<?php
/**
 * 测试短信发送
 */
require('../vendor/autoload.php');

$res = SwaSky\Alisms\Send::verifyCode('手机号',mt_rand(1000,9999),[
    'accessKeyId'   =>  '', //ali acess key
    'accessKeySecret'  => '', //ali access secret
    'signName'  =>  '', //短信签名
    'templateCode'  =>  '' //短信模板code
]);
if($res==0){
    echo '发送失败!';
}else{
    echo '发送成功!';
}