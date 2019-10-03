<?php

/**
 * @since  2019-09-20
 */
namespace Review;

use Review\Exception\ReviewException;
use Review\Redis\Provider;
use Exception;

class Task
{

    /**
     * 获取的任务id数据
     * @var array
     */
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

    /**
     * 设置的任务key
     * @var
     */
    private $key;

    /**
     * 获取的任务个数
     * @var
     */
    private $num;

    /**
     * 指定的字段获取id
     * @var string
     */
    private $uniqueKey = '_id';


    public function __construct()
    {
        $this->provider = new provider();
    }


    /**
     * 设置业务队列key和业务数量
     *
     * @param $key
     * @param $num
     * @param $uniqueKey
     * @return $this
     * @author liu.lei
     */
    public function setTaskKey(string $key, int $num = 3, string $uniqueKey = '_id')
    {

        $this->key = $key;
        $this->num = $num;
        $this->uniqueKey = $uniqueKey ? $uniqueKey : '_id';

        return $this;
    }

    /**
     * 设置回调函数
     *
     * @param \Closure $callback
     * @return $this
     * @author liu.lei
     */
    public function setCallback(\Closure $callback)
    {

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
    public function setTryTimes(int $tryTimes = 3)
    {
        $this->tryTimes = $tryTimes;

        return $this;
    }

    /**
     * 获取待审核任务数据
     * @return array
     */
    public function getWaitTaskIds()
    {
        $this->getTaskIds();
        return $this->ids;
    }

    /**
     *
     */
    private function getTaskIds()
    {
        $tryTimes = 0;
        while (true) {
            for ($i = 0; $i <= $this->num; $i++) {
                $id = $this->provider->getTaskIds($this->key);
                if ($id) {
                    $this->ids[] = $id;
                    // 锁定任务 不锁定任务的话 从池子里取任务有可能会重复
                    $this->provider->addProcessId($this->key, $id);
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
            if ($this->provider->lock($this->key)) {
                $callbackResult = ($this->callback)();
                if (empty($callbackResult)) {
                    $this->provider->unlock($this->key);
                    break;
                } else {
                    //解析结果集
                    $this->resolveResult($callbackResult);
                }
            } else {
                //没有获得锁的睡眠10ms 等待获得锁的将数据写到队列中去
                usleep(100000);
            }
        }
    }

    /**
     * 解析结果集
     * @param $callbackResult
     * @throws Exception
     */
    private function resolveResult($callbackResult)
    {
        foreach ($callbackResult as $result) {
            if (isset($result[$this->uniqueKey])) {
                if (is_object($result[$this->uniqueKey])) {
                    $id = $result[$this->uniqueKey]->__toString();
                } else {
                    $id = $result[$this->uniqueKey];
                }
                $this->provider->addTaskId($this->key, $id);
            } else {
                throw new ReviewException($this->uniqueKey . ':字段对应的数据不存在');
            }
        }
    }

}