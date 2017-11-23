<?php
return [
    'isopen'        =>  true, //是否开启发送短信，默认为打开
    'accessKeyId'   =>  env('ALI_ACCESSKEY'), //阿里云Access ID
    'accessKeySecret'  => env('ALI_ACCESSKEYSECRET'), //阿里云Access Key
    'signName'  =>  env('ALI_SIGNNAME'), //短信签名
    'templateCode'  =>  env('ALI_TEMPCODE'), //短信模板code 验证码模板code
    'logfile'   =>  __DIR__.'/logs/alisms-'.date('Y-m-d').'.log' //日志保存目录
];