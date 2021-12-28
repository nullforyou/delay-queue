<?php

namespace NullForYou\DelayQueue;

interface DelayInterface
{

    public function setGroup(string $groupName, int $delayQueueCount, int $delaySecond);

    public function init();
}