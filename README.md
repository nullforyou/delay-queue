### 延时队列

支持驱动

. rabbitMQ

默认驱动rabbitMQ;

执行命令
```
php artisan vendor:publish --provider="NullForYou\DelayQueue\DelayQueueProvider"
```

然后在`config/delay_queue.php`填写需要的配置；