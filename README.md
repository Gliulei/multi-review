# multi-review
Multi-person review task library to prevent repeated collection tasks, suitable for multi-person audit scenarios

# Installation
composer require liulei/multi-review

# Usage

```
use Review/Task;

$redis = new Redis();
$redis->pconnect('127.0.0.1', '6379');
$task = new Task($redis, $redis);
$ids = $task->setTaskKey('demo')->setCallback(function () {
   $B = new ClientB();
   $B->test();
})->getWaitTaskIds();

var_dump($ids);
```


# Feature
+ 适用于多人审核业务场景
+ 分发任务高效,快速,理论上不会出现重复派单
