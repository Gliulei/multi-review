<?php

/**
 * @since  2019-09-20
 */
namespace review;

use review\redis\provider;
use Exception;

class Task {

    private $ids = [];

    /**
     * 重试次数
     *
     * @var int
     */
    private $tryTimes = 2;

    /**
     * 回调函数
     *
     * @var
     */
    private $callback;

    /**
     * @var provider
     */
    private $provider;

    private $key;

    private $num;

    private $uniqueKey = '_id';


    /**
     * 设置业务队列key和业务数量
     *
     * @param $key
     * @param $num
     * @param $uniqueKey
     * @return $this
     * @author liu.lei
     */
    public function setTaskKey($key, $num, $uniqueKey) {

        $this->key       = $key;
        $this->num       = $num;
        $this->uniqueKey = $uniqueKey;

        return $this;
    }

    /**
     * 设置回调函数
     *
     * @param \Closure $callback
     * @return $this
     * @author liu.lei
     */
    public function setCallback(\Closure $callback) {

        $this->callback = $callback;

        return $this;
    }

    /**
     * 设置重试次数
     *
     * @param int $tryTimes
     * @return $this
     * @author liu.lei
     */
    public function setTryTimes(int $tryTimes) {
        $this->tryTimes = $tryTimes;

        return $this;
    }

    public function getWaitTaskIds($key, $num = 3) {

    }

    private function getTaskIds() {
        $tryTimes = 0;
        $provider = new provider();
        while (true) {
            for ($i = 0; $i <= $this->num; $i++) {
                $id = $provider->getTaskIds($this->key);
                if ($id) {
                    $this->ids[] = $id;
                    //todo 锁定任务 不锁定任务的话 从池子里取任务有可能会重复
                }
                //如果id取够了就跳出循环
                if (count($this->ids) == $this->num) {
                    break;
                }
            }

            //重试目的是 1、任务队列里没有数据 要从池子里取数据放到任务队列
            // 2、redis有可能超时 重试可以容错
            if ($tryTimes++ > $this->tryTimes) {
                break;
            }

            //获取锁成功
            if ($provider->lock($this->key)) {
                $callbackResult = ($this->callback)();
                if (empty($callbackResult)) {
                    $provider->unlock($this->key);
                    break;
                } else {
                    //解析结果集
                    $this->resolveResult($callbackResult);
                }
            } else {
                //睡眠10ms
                usleep(100000);
            }
        }
    }

    private function resolveResult($callbackResult) {
        foreach ($callbackResult as $result) {
            if (isset($result[$this->uniqueKey])) {
                if (is_object($result[$this->uniqueKey])) {
                    $id = $result[$this->uniqueKey]->__toString();
                } else {
                    $id = $result[$this->uniqueKey];
                }

                $this->provider->addTaskId($this->key, $id);
            } else {
                continue;
            }
        }
    }

}