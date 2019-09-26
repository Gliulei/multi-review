<?php

/**
 * @since  2019-09-20
 */
namespace review;

use review\redis\provider;
use Exception;

class Task {

    private $ids = [];

    private $tryTimes = 2;

    private $callback;

    private $args;

    /**
     * 设置回调函数
     *
     * @param \Closure $callback
     * @param array    $args
     * @return $this
     * @author liu.lei
     */
    private function setCallback(\Closure $callback, array $args) {

        $this->callback = $callback;
        $this->args     = $args;

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

    public function getWaitTaskIds($key, $num = 3, $callback = [], $args = []) {

    }

    private function getTaskIds($key, $num) {
        $tryTimes = 0;
        $provider = new provider();
        while (true) {
            for ($i = 0; $i <= $num; $i++) {
                $id = $provider->getTaskIds($key);
                if ($id) {
                    $this->ids[] = $id;
                    //todo 锁定任务 不锁定任务的话 从池子里取任务有可能会重复
                }
                //如果id取够了就跳出循环
                if (count($this->ids) == $num) {
                    break;
                }
            }

            //重试目的是 1、任务队列里没有数据 要从池子里取数据放到任务队列
            // 2、redis有可能超时 重试可以容错
            if ($tryTimes++ > $this->tryTimes) {
                break;
            }
        }
    }

}