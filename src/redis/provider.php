<?php

/**
 * @since  2019-09-20
 */
namespace Review\Redis;

use Redis;

defined('MULTI_REVIEW_TASK_REDIS') || define('MULTI_REVIEW_TASK_REDIS', '127.0.0.1:6379');
defined('MULTI_REVIEW_LIST_REDIS') || define('MULTI_REVIEW_LIST_REDIS', '127.0.0.1:6379');

class Provider
{

    /**
     * @var redis
     */
    private $redis;

    /**
     * @var Redis
     */
    private $listRedis;

    /**
     * 队列任务
     * @var string
     */
    private $task_ids_key = '_multi_review_%s_task_ids_';

    /**
     * 锁
     * @var string
     */
    private $lock_key = '_multi_review_%s_lock_';

    /**
     * 正在处理中的key
     * @var string
     */
    private $process_ids_key = '_multi_review_%s_processing_ids_';


    /**
     * Provider constructor.
     * @param $taskRedis
     * @param $listRedis
     */
    public function __construct($taskRedis, $listRedis)
    {

        $this->redis = $taskRedis;
        $this->listRedis = $listRedis;
    }

    /**
     * pop 获取任务id
     *
     * @param $key
     * @return string
     * @author liu.lei
     */
    public function getTaskIds($key)
    {
        $key = sprintf($this->task_ids_key, $key);

        return $this->listRedis->lPop($key);
    }

    /**
     * 添加任务id到队列末尾
     *
     * @param $key
     * @param $id
     * @return int
     * @author liu.lei
     */
    public function addTaskId($key, $id)
    {
        $key = sprintf($this->task_ids_key, $key);

        return $this->listRedis->rPush($key, $id);
    }

    /**
     * 移除和id相等的元素
     *
     * @param $key
     * @param $id
     * @return int
     * @author liu.lei
     */
    public function remTaskId($key, $id)
    {
        $key = sprintf($this->task_ids_key, $key);

        return $this->listRedis->lRem($key, $id, 0);
    }

    /**
     * https://my.oschina.net/u/1995545/blog/366381
     * 分布式锁
     *
     * @param     $key
     * @param int $expire
     * @return bool
     */
    public function lock($key, $expire = 5)
    {

        $key = sprintf($this->lock_key, $key);

        $is_lock = $this->redis->setnx($key, time() + $expire);
        //没获得锁
        if (!$is_lock) {
            $lock_time = $this->redis->get($key);
            //锁已过期，重置
            if ($lock_time < time()) {
                $is_lock = $this->redis->getSet($key, time() + $expire) < time();
            }

            return $is_lock;
        }

        return $is_lock;
    }

    /**
     * 解锁，默认立即解开，seconds设置多少秒后解开
     * @param $key
     * @param int $seconds
     * @return bool|int
     */
    public function unlock($key, $seconds = 0)
    {
        $key = sprintf($this->lock_key, $key);
        if ($seconds) {
            return $this->redis->getSet($key, time() + $seconds);
        }
        return $this->redis->del($key);
    }


    /**
     * 添加正在处理的ID
     *
     * @param $key
     * @param $value
     * @return int
     */
    public function addProcessId($key, $value)
    {
        $key = $this->getProcessKey(sprintf($this->process_ids_key, $key));
        $this->redis->expire($key, 3600);
        return $this->redis->zAdd($key, time(), $value);
    }

    /**
     * 判断是否处于正在处理中，如果超过$takenTtl分钟则解除
     *
     * @param $key
     * @param $id
     * @param $maxTtl
     * @return bool
     */
    public function isProcessing($key, $id, $maxTtl = 10)
    {
        $key = $this->getProcessKey(sprintf($this->process_ids_key, $key));
        $takenTime = $this->redis->zScore($key, $id);
        if ($takenTime) {
            if (time() - $takenTime > $maxTtl * 60) {
                $this->redis->zRem($key, $id);
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    private function getProcessKey($key)
    {
        $date = date('Ymd');
        $key = $key . $date . '_';
        return $key;
    }

    /**
     * 删除正在处理的ID
     * @param $key
     * @param $value
     * @return int
     */
    public function delProcessId($key, $value)
    {
        $key = $this->getProcessKey(sprintf($this->process_ids_key, $key));
        return $this->redis->zRem($key, $value);
    }
}