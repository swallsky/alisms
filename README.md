# AliSMS
阿里云短信服务开发工具包


## 开始使用

### composer

- `composer require swallsky/alisms`


### 使用方法

```php
//require('../vendor/autoload.php'); //加载composer

$res = SwaSky\Alisms\Send::verifyCode('手机号',mt_rand(1000,9999),[
    'accessKeyId'   =>  '', //阿里云 acess key
    'accessKeySecret'  => '', //阿里云 access secret
    'signName'  =>  '', //短信签名
    'templateCode'  =>  '' //短信模板code
]);
if($res==0){
    echo '发送失败!';
}else{
    echo '发送成功!';
}
```

## 获取Access ID和Access Key
[如何获取Access ID和Access Key](https://help.aliyun.com/knowledge_detail/38738.html)

## 短信相关操作
[短信签名](https://help.aliyun.com/document_detail/55327.html?spm=5176.8195934.507901.5.KZkgsL)
[短信模板](https://help.aliyun.com/document_detail/55330.html?spm=5176.doc55327.6.544.lhzuXh)

