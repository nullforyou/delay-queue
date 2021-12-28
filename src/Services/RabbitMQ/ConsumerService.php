<?php


namespace NullForYou\DelayQueue\Services\RabbitMQ;


use NullForYou\DelayQueue\Services\DelayConsumerInterface;

class ConsumerService extends DelayService implements DelayConsumerInterface
{

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