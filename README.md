# php-redis-v1 第一版上线
<h3>使用说明：</h3>
<p>本类采用php单例模式</p>
<p>第一步：<pre>require '/php-redis-v1/RedisMeal.class.php'</pre></p>
<p>第二步：<pre>$object::getInstance()</pre></p>
<p>第三步：根据需求选择加入队列，还是发送邮件</p>
<pre>加入队列：
$object->joinQueue($param);

</pre>
<pre>
参数示例:
[
    'key' => 'reg_email', //redis里的key
    'value' => [
        '996674366@qq.com', //收件人
        'liuzhongsheng@xxx.cn'//收件人
    ]
]
</pre>

<pre>发送邮件：
$object->sandEmail($param);
</pre>
<pre>
参数示例:
[
    'key'=>[
        'key'=>'要查询的key',
        'mode'=>'开启阻塞模式为block',
        'timeOut'=>'如果mode为block，可以传入超时秒数，默认100秒'
    ],
    'title'=>'测试邮件标题',
    'subject'=>'测试邮件主题',
    'content'=>'测试邮件内容'
 ]
</pre>
<h3>配置说明<span>(参考config.php)</span>：</h3>

<p>config.php<p>
<pre>

return [
    'start_using'       =>  'off',  //on 开 off关闭
    'host'              =>  '127.0.0.1',    //服务地址
    'port'              =>  6379,   //服务端口号
    'email_host'        =>  'smtp.163.com',//smtp服务器的名称
    'email_smtp_auth'   =>  true, //启用smtp认证
    'email_user_name'   =>  '',//发件人
    'email_from'        =>  '',//发件人地址
    'email_from_name'   =>  '',//发件人姓名
    'email_passwrod'    =>  '',//邮箱密码：此密码未客户端授权密码
    'email_charset'     =>  'utf-8',//设置邮件编码
    'email_is_html'     =>  true, // 是否HTML格式邮件
];
</pre>

<p>以上为本程序使用方式欢迎大家提提建议或者加入QQ群：456605791 交流，如果觉得代码写得还行请赞一个谢谢,欢迎提出更好的解决办法<p>
<b>url:<a href='https://www.php63.cc'>https://www.php63.cc</a></b>