<?php


namespace RabbitMq;



use NullForYou\DelayQueue\Services\RabbitMQ\ProducerService;
use PHPUnit\Framework\TestCase;

class ProducerTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testSendMessage()
    {
        $product = new ProducerService('127.0.0.1', '5672', 'admin', 'admin');
        $product->setGroup('user', 5, 6000);
        $product->init();
        $product->push(['id' => 1], 10);

        $this->assertTrue(true);
    }
}