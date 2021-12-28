<?php


namespace RabbitMq;


use NullForYou\DelayQueue\Services\RabbitMQ\ConsumerService;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;

class ConsumerTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testConsumer()
    {
        $consumer = new ConsumerService('127.0.0.1', '5672', 'admin', 'admin');
        $consumer->setGroup('user', 5, 6000);
        $consumer->init();
        $callback = function (AMQPMessage $message) {
            $array = json_decode($message->getBody(), true);
            echo sprintf("已消费:%d/n", $array['id']);
            //确认消息处理完成
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        };
        $consumer->consume($callback);

        $this->assertTrue(true);
    }

}