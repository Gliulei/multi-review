<?php

/**
 * Created by PhpStorm.
 * User: liulei
 * Date: 2019/10/3
 * Time: 8:42 PM
 */
require_once '../vendor/autoload.php';

use Review\Task;

class ClientTest
{
    public function wait()
    {
//        $redis = new Redis();
//        $redis->pconnect('127.0.0.1', '6379');
        $redis = new stdClass();
        $task = new Task($redis, $redis);
        $ids = $task->setTaskKey('demo')->setCallback(function () {
            $B = new ClientB();
            $B->test();
        })->getWaitTaskIds();

        return $ids;
    }
}

$clientTest = new ClientTest();
$clientTest->wait();