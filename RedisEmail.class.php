<?php
// +----------------------------------------------------------------------
// | redis邮件发送类
// +----------------------------------------------------------------------
// | Copyright (c) www.php63.cc All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 吹泡泡的鱼 <996674366@qq.com>
// +---------
//use PHPMailer\PHPMailer\PHPMailer;
require dirname(__FILE__).'/PHPMailer/class.smtp.php';
require dirname(__FILE__).'/PHPMailer/class.phpmailer.php';


class RedisEmail
{
    /**
     * 为了避免类被重复实例化，第一次实例化后将会把实例化后的结果存入该方法
     * @var
     */
    private static $instance;

    /**
     * @var 配置项
     */
    private $config;
    private $redis;

    //初始化化类，防止被实例化
    private function __construct()
    {
        $this->redis = $this->connect();
    }

    //防止类被克隆
    private function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * 防止类重复实例化
     * 检测当前类是否已经实例化过，如果实例化过直接返回
     * @return redisEmail 返回实例化过后的对象
     */
    public static function getInstance()
    {
        //检测当前类是否实例化过
        if (!(self::$instance instanceof self)) {
            //如果当前类没有实例化过则实例化当前类
            self::$instance = new self;
        }
        return self::$instance;
    }

    //连接redis
    private function connect()
    {
        try {
            //引入配置文件
            $this->config = include 'config.php';
            $redis = new \Redis();
            $redis->pconnect($this->config['host'], $this->config['port']);
            return $redis;
        } catch (RedisException $e) {
            echo 'phpRedis扩展没有安装：' . $e->getMessage();
            exit;
        }
    }

    /**
     * 加入队列
     * 参数以数组方式传递，key为键名，value为要写入的值，value，如果需要写入多个则以数组方式传递
     * @param array 要加入队列的格式 ['key'=>'键名','value'=>[值]]
     * @return int 成功返回 1失败 返回0
     */
    public function joinQueue($param = [])
    {
        //如果value不存在或者不是一个数组则写入一次
        if ((array)$param['value'] !== $param['value']) {
            return $this->redis->lpush($param['key'], $param['value']);
        }
        //如果是一个数组则循环写入
        foreach ($param['value'] as $value) {
            $this->redis->lpush($param['key'], $value);
        }
    }

    /** 移除队列
     * @param array $param ['key'=>'要查找的key','mode'=>'请求方式','timeOut'=>'超时时间']
     * @return mixed
     */
    public function popQueue($param = [])
    {
        if (!array_key_exists('mode', $param)) {
            //如果没指定用阻塞模式则用rpop非阻塞模式
            $param['mode'] = 'noBlock';
        } else {
            if (!array_key_exists('timeOut', $param)) {
                //如果没指定超时时间默认100秒
                $param['timeOut'] = 100;
            }
        }

        if ($param['mode'] == 'block') {
            return $this->redis->brpop($param['key'], $param['timeOut']);
        } else {
            return $this->redis->rpop($param['key']);
        }
    }

    /**
     * 邮件发送方法
     * 传入要处理的数组，包含内容如下：
     * key:队列中的key，格式$param['key'=>['key'=>'要查询的key','mode'=>'开启阻塞模式为block','timeOut'=>'如果mode为block，可以传入超时秒数，默认100秒']]
     * title:邮件标题
     * subject:邮件主题
     * content:内容
     * @param array $param
     * @return bool
     */
    public function sendEmail($param = [])
    {
        $param['email'] = $this->popQueue(['key'=>$param['key']]);
        //检测是否传入邮箱标题
        if (!array_key_exists('title', $param['title'])) {
            $param['title'] = '无标题';
        }

        //检测是否传入邮件主题
        if (!array_key_exists('subject', $param['subject'])) {
            $param['subject'] = '无主题';
        }

        //检测是否传入邮件内容
        if (!array_key_exists('content', $param['content'])) {
            $param['content'] = '无内容';
        }

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->Host     = $this->config['email_host'];
        $mail->SMTPAuth = $this->config['email_smtp_auth'];
        $mail->Username = $this->config['email_user_name'];
        $mail->Password = $this->config['email_passwrod'];
        $mail->From     = $this->config['email_from'];
        $mail->FromName = $this->config['email_from_name']; //发件人姓名
        $mail->AddAddress($param['email'], $param['title']);
        $mail->WordWrap = 50; //设置每行字符长度
        $mail->IsHTML($this->config['email_is_html']); // 是否HTML格式邮件
        $mail->CharSet  = $this->config['email_charset']; //设置邮件编码
        $mail->Subject  = $param['subject']; //邮件主题
        $mail->Body     = $param['content']; //邮件内容
        $mail->AltBody  = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
        $result = $mail->Send();
        return $result;
    }
}