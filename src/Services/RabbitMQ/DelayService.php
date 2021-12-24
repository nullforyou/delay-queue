<?php

namespace NullForYou\DelayQueue\Services\RabbitMQ;


use NullForYou\DelayQueue\Services\DelayInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class DelayService implements DelayInterface
{

    private $host;

    private $port;

    private $user;

    private $password;

    private $vhost = '/';

    //交换机名称
    public $exchangeName;

    //普通队列名称和路由key
    public $queueName;
    public $routeKey;

    //延迟队列和路由
    public $delayQueueName;
    public $delayRouteKey;

    //延迟时长
    public $delaySecond;  //生命周期

    public $channel;

    public function __construct()
    {
        $this->host =           config('delay_queue.rabbit_mq.host');
        $this->port =           config('delay_queue.rabbit_mq.port');
        $this->user =           config('delay_queue.rabbit_mq.user');
        $this->password =       config('delay_queue.rabbit_mq.password');
        $this->exchangeName =   config('delay_queue.rabbit_mq.exchange_name', 'exchange_name');
        $this->queueName =      config('delay_queue.rabbit_mq.queue_name', 'queue_name');
        $this->routeKey =       config('delay_queue.rabbit_mq.route_key', 'route_key');
        $this->delayQueueName = sprintf('delay_%s', $this->queueName);
        $this->delayRouteKey =  sprintf('delay_%s', $this->routeKey);
        $this->delaySecond =    config('delay_queue.rabbit_mq.delaySecond', 86400);
        $this->connection();
    }

    /**
     * @throws \Exception
     */
    private function connection()
    {
        try {
            $connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password);
            $this->channel = $connection->channel();
            $this->init();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function init()
    {
        // 声明交换机
        $this->channel->exchange_declare($this->exchangeName, 'direct', false, true, false);
        $this->declareConsumeQueue();
        $this->declareDelayQueue();
    }

    //消费队列
    private function declareConsumeQueue()
    {
        //声明消费队列
        $this->channel->queue_declare($this->queueName, false, true, false, false);
        //绑定交换机及队列
        $this->channel->queue_bind($this->queueName, $this->exchangeName, $this->routeKey);
    }

    //延迟队列
    private function declareDelayQueue()
    {
        //设置消息过期时间
        $tab = new AMQPTable([
            'x-dead-letter-exchange' => $this->exchangeName,    //消息过期后推送至此交换机
            'x-dead-letter-routing-key' => $this->routeKey,        //消息过期后推送至此路由地址        //也就是我们消费的正常队列    与①对应
            'x-message-ttl' => intval($this->delaySecond) * 1000, //毫秒
        ]);
        //声明延迟队列
        $this->channel->queue_declare($this->delayQueueName,false,true,false,false,false, $tab);
        //绑定交换机及延迟队列
        $this->channel->queue_bind($this->delayQueueName, $this->exchangeName, $this->delayRouteKey);
    }

    //入队列
    public function push(array $payload, int $expiration)
    {
        //创建消息
        $msg = new AMQPMessage(json_encode($payload), [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'expiration' => $expiration * 1000
        ]);
        //推送至队列                   //消息   //交换机名称        //路由  推送至延迟队列中
        $this->channel->basic_publish($msg, $this->exchangeName, $this->delayRouteKey);
    }

    //出队列
    public function consume($callback)
    {
        //消费  普通消费队列
        $this->channel->basic_consume($this->queueName, '', false, false, false, false, $callback);
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }
}