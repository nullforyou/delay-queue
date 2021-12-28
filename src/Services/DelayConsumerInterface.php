<?php


namespace NullForYou\DelayQueue\Services;


use NullForYou\DelayQueue\DelayInterface;

interface DelayConsumerInterface extends DelayInterface
{

    /**
     * @param $callback
     * @return void
     */
    public function consume($callback);
}