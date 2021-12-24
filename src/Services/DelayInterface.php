<?php

namespace NullForYou\DelayQueue\Services;

interface DelayInterface
{

    /**
     * @param array $payload
     * @param int $expiration
     * @return void
     */
    public function push(array $payload, int $expiration);

    /**
     * @param $callback
     * @return void
     */
    public function consume($callback);
}