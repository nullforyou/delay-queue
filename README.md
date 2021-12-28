### 延时队列

目前仅支持laravel框架；
支持驱动

. rabbitMQ

默认驱动rabbitMQ;

执行命令
```
php artisan vendor:publish --provider="NullForYou\DelayQueue\DelayQueueProvider"
```

然后在`config/delay_queue.php`填写需要的配置；

###延迟队列组
根据业务可以设置队列组，每个组可以设置延迟队列数量，可根据数据量自行设置延迟队列个数。如果不设置，将采用默认设值。
例如：
```
...

'queue_groups' => [
    'default' => [
        'delay_queue_count' => 1,
        'delay_second' => 86400,
    ],
    'order' => [
        'delay_queue_count' => 5,
        'delay_second' => 86400,
    ],
],
    
...

```


###生产者

使用时只需要依赖注入`NullForYou\DelayQueue\Services\DelayProducerInterface`即可，在服务提供者注册时根据配置的`delay_driver`进行服务注册。
在生产队列消息时，可以设置队列组

```
$this->producerService->setGroup('order');
```

也可以在推送消息时一起设置

```
$this->producerService->push($event->getOrder()->toArray(), 6000, 'order');
```