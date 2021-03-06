<?php
/**
 * Created by PhpStorm.
 * User: qinweige
 * Date: 2018/3/23
 * Time: 17:26:24
 */

use Workerman\Worker;

// Autoload
require_once __DIR__ . '/../../vendor/workerman/workerman/Autoloader.php';
// redis实例不要在这里连接,在worker里连
$redisHost = '127.0.0.1';
$redisPort = 6379;
// 队列名称(在redis中的键名)
$QueueName = 'pdf-queue';

// =============================================================================

// 创建一个Worker监听2345端口，使用http协议通讯
$pdf_worker = new Worker("tcp://0.0.0.0:2345");
// 设置worker名称
$pdf_worker->name = 'addTaskWorker';
// 启动4个进程对外提供服务
$pdf_worker->count = 1;
$pdf_worker->redis = new \Redis;

// worker子进程启动时回调
$pdf_worker->onWorkerStart = function ($worker) {
    global $redisHost, $redisPort;
    $worker->redis->pconnect($redisHost, $redisPort, 1);
};

// worker子进程停止时回调
$pdf_worker->onWorkerStop = function ($worker) {
    $worker->redis->close();
};

/**
 * 接收到数据时回调
 * @param Object $connection 当前连接
 * @param mixed $data 来自客户端的数据
 * 要向客户端返回信息请在该回调内使用 $connection->send($msg)
 */
$pdf_worker->onMessage = function ($connection, $data) use ($pdf_worker) {
    global $QueueName;

    $result = [
        'code' => 0,
        'msg' => 'task added'
    ];
    $data = rtrim($data);
    $recData = json_decode($data, true);
    if (!is_array($recData)) {
        // 这里可以log无效数据
        $result = [
            'code' => 1,
            'msg' => 'invalid task'
        ];
    } else {
        $pdf_worker->redis->rPush($QueueName, $data);
    }
    // 向客户端发送结果
    $connection->send(json_encode($result));
};

// =============================================================================

// 创建一个Worker用来执行任务
$task_worker = new Worker();
// 设置worker名称
$task_worker->name = 'createPdfWorker';
// 启动4个进程
$task_worker->count = 1;
$task_worker->redis = new \Redis;

// worker子进程启动时回调
$task_worker->onWorkerStart = function ($worker) {
    global $redisHost, $redisPort, $QueueName;
    $worker->redis->pconnect($redisHost, $redisPort, 1);
    while (true) {
        $task = $worker->redis->rPop($QueueName);
        // $redis = new Redis;
        // $redis->rPop($QueueName);
        if ($task) {
            echo $task . PHP_EOL;
        }
        sleep(1);
    }
};
// worker子进程停止时回调
$task_worker->onWorkerStop = function ($worker) {
    $worker->redis->close();
};

// =============================================================================

// 运行worker
Worker::runAll();

