<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Container;

use Psr\Container\ContainerInterface;
use WeakReference;
use Yiisoft\Test\Support\Container\Exception\NotFoundException;
use Yiisoft\Test\Support\Container\WeakRef\Result;

/**
 * The WeakRefContainer class is for debugging application state.
 * If you expect your application's services to be stateless, then the WeakRefContainer can help you verify this.
 */
class WeakRefIsolatedContainer implements ContainerInterface
{
    private array $values;
    /** @var WeakReference[] */
    private array $weakMap = [];
    private ?Result $result = null;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException($id);
        }
        return $this->values[$id];
    }
    public function has($id)
    {
        return array_key_exists($id, $this->values);
    }

    public function runIsolated(\Closure $context): Result
    {
        if ($this->result !== null) {
            throw new \RuntimeException(
                'The WeakRefIsolatedContainer cannot be run twice. Use getResult() method to get first run result.'
            );
        }
        $this->prepareWeakRefs();
        $context($this);
        $this->prepareResult();
        return $this->result;
    }
    public function getResult(): Result
    {
        if ($this->result !== null) {
            throw new \RuntimeException(
                'The WeakRefIsolatedContainer has not started yet.'
            );
        }
        return $this->result;
    }

    private function prepareWeakRefs(): void
    {
        foreach ($this->values as $id => $value) {
            if (!is_object($value)) {
                continue;
            }
            $this->weakMap[$id] = WeakReference::create($value);
        }
    }
    private function prepareResult(): void
    {
        $result = [
            Result::COUNT_TOTAL => count($this->values),
            Result::COUNT_OBJECTS => 0,
            Result::COUNT_NOT_OBJECTS => 0,
        ];
        foreach ($this->values as $id => &$value) {
            $result[is_object($value) ? Result::COUNT_OBJECTS : Result::COUNT_NOT_OBJECTS] += 1;
            unset($value);
        }
        $this->values = [];
        foreach ($this->weakMap as $id => $weakRef) {
            if ($weakRef->get() === null) {
                unset($this->weakMap[$id]);
            }
        }
        $result[Result::COUNT_LEAKS] = count($this->weakMap);
        $result[Result::LEAKED_IDS] = array_keys($this->weakMap);
        $this->weakMap = [];

        $this->result = new Result($result);
    }
}
