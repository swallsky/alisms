<?php
/*
 * 短信发送api 接口升级
 * (c) Swall Sky <xjz1688@163.com>
 */
namespace SwaSky\Alisms;

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// 加载区域结点配置
Config::load();

class Sms
{
    /**
     * @var null 实例对象
     */
    private static $_instance = null;

    /**
     * @var array 配置信息
     */
    private static $_config = [];

    /**
     * composer安装目录
     * @var null
     */
    private $_venderPath = null;

    /**
     * 初始化短信发送类
     * Send constructor.
     */
    private function __construct()
    {
        $this->_venderPath = dirname(dirname(dirname(dirname(__DIR__)))); //默认路径
    }

    /**
     * 当要调用的方法不存在或权限不足时，会自动调用__call 方法
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        $static = self::_getInstance();
        if(method_exists($static,$name)){
            $arguments = func_get_args();
            $res = call_user_func_array([$static,$name],$arguments[1]);//调用对应的方法
            return $res!==null?$res:$static;
        }else{
            throw new Exception($name.' method is not exists');
        }
    }

    /**
     * 当调用的静态方法不存在或权限不足时，会自动调用__callStatic方法。
     * @param $name
     * @param $arguments
     * @return static
     */
    public static function __callStatic($name,$arguments)
    {
        $static = self::_getInstance();
        if(method_exists($static,$name)){
            $arguments = func_get_args();
            $res = call_user_func_array([$static,$name],$arguments[1]);//调用对应的方法
            return $res!==null?$res:$static;
        }else{
            throw new Exception($name.' method is not exists');
        }
    }

    /**
     * 获取实例对象
     * @return null
     */
    private static function _getInstance()
    {
        if(!isset(self::$_instance))//判断使用的数据库是否已经初始化
            self::$_instance =new self();	//若当前对象实例不存在
        return self::$_instance;  	//调用对象私有方法连接 数据库
    }

    /**
     * 设置vender路径
     * @param $path
     */
    protected function setVenderPath($path)
    {
        $this->_venderPath = $path;
    }

    /**
     * 返回vender路径
     * @return null|string
     */
    protected function getVenderPath()
    {
        return $this->_venderPath;
    }

    /**
     * 设置配置信息
     * @param array $config
     */
    protected function setConfig($config = [])
    {
        if(empty($config)){//如果参数为空，直接读取config/alisms.php配置文件
            $config = include($this->getConfigFile());
        }
        $logdefile = $this->getVenderPath().'/logs/alisms-'.date('Y-m-d').'.log';//默认的logfile路径
        return static::$_config = [
            'isopen'    =>  isset($config['isopen'])?$config['isopen']:true, //是否开启短信验证
            'accessKeyId'   =>  $config['accessKeyId'], //阿里云 accesskey
            'accessKeySecret'  => $config['accessKeySecret'], //阿里云accesskeysecret
            'signName'  =>  $config['signName'], //短信签名
            'product'   =>  'Dysmsapi',
            'domain'    =>  'dysmsapi.aliyuncs.com',
            'region'    =>  'cn-hangzhou',
            'logfile'   =>  empty($config['logfile'])?$logdefile:$config['logfile'] //错误日志记录文件
        ];
    }
    /**
     * 获取当前配置信息
     * @return mixed
     */
    public function getConfig()
    {
        $config = self::$_config;
        return empty($config)?$this->setConfig():$config;
    }
    /**
     * 设置配置文件路径
     * @param $file 配置文件
     */
    protected function setConfigFile($file)
    {
        self::$_configFile = $file;
    }

    /**
     * 返回配置文件路径
     * @return string
     */
    protected function getConfigFile()
    {
        return empty(self::$_configFile)?
            $this->getVenderPath().'/config/alisms.php': //默认路径
            static::$_configFile;
    }

    /**
     * 发送信息
     * @param string $mobile 发送的手机号 例如18610101010
     * @param array $data 需要替换的变量
     * @param string $tplcode 模版CODE
     * @return int 1:成功 0:失败
     */
    protected function send($mobile,$data,$tplcode)
    {
        $config = $this->getConfig(); //读取配置信息
        if($config['isopen']){//开启短信发送
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
            $request->setTemplateCode($tplcode);
            // 可选，设置模板参数
            $request->setTemplateParam(json_encode($data));
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
        }else{
            $log = new Logger('AliyunSms');
            $log->pushHandler(
                new StreamHandler(
                    $config['logfile'],
                    Logger::ERROR
                )
            );
            $log->error('Aliyun sms error',['code'=>'000000','message'=>'The SMS is half closed,please check isopen param.']);
            return 1;
        }
    }
}