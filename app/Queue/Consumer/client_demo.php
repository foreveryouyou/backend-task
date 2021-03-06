<?php
/**
 * Created by PhpStorm.
 * User: qinweige
 * Date: 2018/3/23
 * Time: 16:51
 */

// 连接队列服务，ip地址为队列服务端ip，这里假设是本机部署ip为127.0.0.1
$client = stream_socket_client("tcp://127.0.0.1:1236", $err_no, $err_msg, 5);
if(!$client)
{
    exit($err_msg);
}
// 一个邮件任务
$message = array(
    'class' => 'Mail',
    'method' => 'send',
    'args' => array('xiaoming', 'xiaowang', 'hello'),
);
// 数据末尾加一个换行，使其符合Text协议。使用json编码
$message = json_encode($message)."\n";
// 向队列发送任务，让队列慢慢去执行
fwrite($client, $message);
// 队列返回的结果，这个结果是立即返回的
echo fread($client, 8192);