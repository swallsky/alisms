<?php
/*
 * 短信发送api
 * (c) Swall Sky <xjz1688@163.com>
 */
namespace SwaSky\Alisms;

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
//vendor path
defined('VENDOR_PATH') or define('VENDOR_PATH',dirname(dirname(dirname(__DIR__))));

// 加载区域结点配置
Config::load();

class Send
{
    /**
     * 读取配置文件
     * @param array $config
     * @return array
     */
    public static function config($config = [])
    {
        if(empty($config)){//如果参数为空，直接读取config/alisms.php配置文件
            $config = include(VENDOR_PATH.'/config/alisms.php');
        }
        if(is_string($config)){//如果字符直接读取配置文件名
            $config = include(VENDOR_PATH.'/'.$config.'.php');
        }
        return [
            'accessKeyId'   =>  $config['accessKeyId'], //阿里云 accesskey
            'accessKeySecret'  => $config['accessKeySecret'], //阿里云accesskeysecret
            'signName'  =>  $config['signName'], //短信签名
            'templateCode'  =>  $config['templateCode'], //短信模板code
            'product'   =>  'Dysmsapi',
            'domain'    =>  'dysmsapi.aliyuncs.com',
            'region'    =>  'cn-hangzhou',
            'logfile'   =>  VENDOR_PATH.'/logs/alisms-'.date('Y-m-d').'.log' //错误日志记录文件
        ];
    }
    /**
     * @param $mobile 发送的手机号 例如18610101010
     * @param $number 发送的随机码
     * @return int 1:成功 0:失败
     */
    public static function verifyCode($mobile,$number,$config = [])
    {
        $config = self::config($config);
        // 初始化用户Profile实例
        $profile = DefaultProfile::getProfile($config['region'], $config['accessKeyId'], $config['accessKeySecret']);
        // 增加服务结点
        DefaultProfile::addEndpoint($config['region'], $config['region'], $config['product'], $config['domain']);
        // 初始化AcsClient用于发起请求
        $acsClient = new DefaultAcsClient($profile);
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();
        // 必填，设置雉短信接收号码
        $request->setPhoneNumbers($mobile);
        // 必填，设置签名名称
        $request->setSignName($config['signName']);
        // 必填，设置模板CODE
        $request->setTemplateCode($config['templateCode']);
        // 可选，设置模板参数
        $request->setTemplateParam(json_encode(['number'=>$number]));
        // 发起访问请求
        $acsResponse = $acsClient->getAcsResponse($request);
        if($acsResponse->Code=='OK'){//发送成功
            return 1;
        }else{//发送失败
            $log = new Logger('AliyunSms');
            $log->pushHandler(
                new StreamHandler(
                    $config['logfile'],
                    Logger::ERROR
                )
            );
            $log->error('Aliyun sms error',['code'=>$acsResponse->Code,'message'=>$acsResponse->Message]);
            return 0;
        }
    }
}