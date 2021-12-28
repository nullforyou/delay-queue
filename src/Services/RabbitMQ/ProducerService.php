<?php


namespace NullForYou\DelayQueue\Services\RabbitMQ;


use NullForYou\DelayQueue\Services\DelayProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class ProducerService extends DelayService implements DelayProducerInterface
{

    //入队列
    public function push(array $payload, int $expiration)
    {
        //创建消息
        $this->publish(
            new AMQPMessage(json_encode($payload), [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'expiration' => $expiration * 1000
            ])
        );
    }
}