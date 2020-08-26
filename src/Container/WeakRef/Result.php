<?php

namespace Yiisoft\Test\Support\Container\WeakRef;

class Result
{
    public const COUNT_TOTAL = 0;
    public const COUNT_OBJECTS = 1;
    public const COUNT_NOT_OBJECTS = 2;
    public const COUNT_LEAKS = 3;
    public const LEAKED_IDS  = 4;

    private array $results;
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function get(int $key)
    {
        if (!key_exists($key, $this->results)) {
            throw new \RuntimeException('Value not specified.');
        }
        return $this->results[$key];
    }

    public function hasLeaks(): bool
    {
        return $this->get(self::COUNT_LEAKS) > 0;
    }
}
