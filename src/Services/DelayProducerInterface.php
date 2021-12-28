<?php


namespace NullForYou\DelayQueue\Services;


use NullForYou\DelayQueue\DelayInterface;

interface DelayProducerInterface extends DelayInterface
{

    public function push(array $payload, int $expiration);
}