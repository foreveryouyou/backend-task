<?php
/**
 * Created by PhpStorm.
 * User: qinweige
 * Date: 2018/3/23
 * Time: 14:12
 */

/**
 * 消费者逻辑：生成pdf
 * Class Pdf
 */
class Pdf
{
    public function create($task)
    {
        // 作为例子，代码省略
        sleep(5);
        echo json_encode($task) . " created success\n";
    }
}