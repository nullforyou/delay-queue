<?php

namespace NullForYou\DelayQueue;


use Illuminate\Support\ServiceProvider;
use NullForYou\DelayQueue\Services\DelayInterface;
use NullForYou\DelayQueue\Services\RabbitMQ\DelayService;

class DelayQueueProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . "/config/delay_queue.php" => config_path("delay_queue.php")
        ]);

        $this->mergeConfigFrom(__DIR__ . "/config/delay_queue.php", "delay_queue");
    }

    public function register()
    {
        switch (config('delay_queue.delay_driver')) {
            case 'rabbit_mq':
                $this->app->bind(DelayInterface::class, DelayService::class);
                break;
        }
    }
}