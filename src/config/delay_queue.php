<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 默认的延迟队列驱动
    |--------------------------------------------------------------------------
    |
    | 默认以rabbit MQ 驱动延时队列，如果需要redis或其他的需要完成程序编码
    |
    */
    'delay_driver' => env('DELAY_DRIVER', 'rabbit_mq'),

    /*
    |--------------------------------------------------------------------------
    | rabbit MQ驱动
    |--------------------------------------------------------------------------
    */
    'rabbit_mq' => [
        'host' => env('DELAY_RABBIT_MQ_HOST', '127.0.0.1'),
        'port' => env('DELAY_RABBIT_MQ_PORT', '5672'),
        'user' => env('DELAY_RABBIT_MQ_USER', ''),
        'password' => env('DELAY_RABBIT_MQ_PASSWORD', ''),

        'exchange_name' => env('DELAY_RABBIT_MQ_EXCHANGE_NAME', 'exchange_name'),
        'queue_name' => env('DELAY_RABBIT_MQ_QUEUE_NAME', 'queue_name'),
        'route_key' => env('DELAY_RABBIT_MQ_ROUTE_KEY', 'route_key'),
        'delay_second' => env('DELAY_RABBIT_MQ_DELAY_SECOND', 86400),
    ],
];