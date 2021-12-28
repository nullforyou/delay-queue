<?php

namespace NullForYou\DelayQueue\Services\RabbitMQ;


use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class DelayService
{

    protected $host;

    protected $port;

    protected $user;

    protected $password;

    /** @var string 交换机名称 */
    protected $exchangeName;

    /** @var string 队列名称 */
    protected $queueName;

    /** @var string 队列路由 */
    protected $routeKey;

    /** @var string 延迟队列前缀 */
    protected $delayQueueNamePrefix;

    /** @var string 延时队列路由前缀 */
    protected $delayRoutePrefix;

    /** @var int 延迟队列组内个数 */
    protected $delayQueueCount;

    /** @var integer 延迟时长 */
    protected $delaySecond;

    /** @var AMQPChannel 通道 */
    protected $channel;

    public function __construct(string $host, int $port, string $user, string $password)
    {
        $this->host =           $host;
        $this->port =           $port;
        $this->user =           $user;
        $this->password =       $password;
    }

    /**
     * @param string $groupName
     * @param int $delayQueueCount
     * @param int $delaySecond
     */
    public function setGroup(string $groupName, int $delayQueueCount, int $delaySecond)
    {
        $this->exchangeName = sprintf('%s_exchange', $groupName);
        $this->queueName = sprintf('%s_queue', $groupName);
        $this->routeKey = sprintf('%s_route_key', $groupName);
        $this->delayQueueNamePrefix = sprintf('%s_delay_queue', $groupName);
        $this->delayRoutePrefix = sprintf('%s_delay_route_key', $groupName);
        $this->delayQueueCount = $delayQueueCount;
        $this->delaySecond = $delaySecond;
    }

    /**
     * @throws \Exception
     */
    public function init()
    {
        try {
            $connection = new AMQPStreamConnection($this->host, $this->port, $this->user, $this->password);
            $this->channel = $connection->channel();
            // 声明交换机
            $this->channel->exchange_declare($this->exchangeName, 'direct', false, true, false);
            $this->declareConsumeQueue();
            $this->declareDelayQueue();
        } catch (\Exception $exception) {
            throw $exception;
        }
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
        for ($i = $this->delayQueueCount; $i > 0; $i --) {
            $delayQueueName = sprintf('%s_%d', $this->delayQueueNamePrefix, $i);
            $delayRouteKey = sprintf('%s_%d', $this->delayRoutePrefix, $i);
            //设置消息过期时间
            $tab = new AMQPTable([
                'x-dead-letter-exchange' => $this->exchangeName,    //消息过期后推送至此交换机
                'x-dead-letter-routing-key' => $this->routeKey,        //消息过期后推送至此路由地址
                'x-message-ttl' => intval($this->delaySecond) * 1000, //毫秒
            ]);
            //声明延迟队列
            $this->channel->queue_declare($delayQueueName,false,true,false,false,false, $tab);
            //绑定交换机及延迟队列
            $this->channel->queue_bind($delayQueueName, $this->exchangeName, $delayRouteKey);
        }
    }

    /**
     * @param AMQPMessage $message
     */
    protected function publish(AMQPMessage $message)
    {
        //延迟队列路由
        $range = range(1, $this->delayQueueCount);
        $currentRouteKey = sprintf('%s_%d', $this->delayRoutePrefix, $range[time() % $this->delayQueueCount]);
        //推送至队列                   //消息   //交换机名称        //路由  推送至延迟队列中

        $this->channel->basic_publish($message, $this->exchangeName, $currentRouteKey);
    }

}