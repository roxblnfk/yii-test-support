<?php

declare(strict_types=1);

namespace Yiisoft\Test\Support\Tests\Container;

use Psr\Container\ContainerInterface;
use Yiisoft\Test\Support\Container\WeakRefIsolatedContainer;

final class WeakRefIsolatedContainerTest extends BaseContainerTest
{
    public function testStatefulServiceLeaksDetection()
    {
        $container = $this->createContainer(['object' => new \DateTimeImmutable()]);
        $state = null;
        $context = function (ContainerInterface $container) use (&$state) {
            $state = $container->get('object');
        };

        $result = $container->runIsolated($context);

        $this->assertTrue($result->hasLeaks());
    }
    public function testStatelessServiceLeaksDetection()
    {
        $container = $this->createContainer(['object' => new \DateTimeImmutable()]);
        $context = function (ContainerInterface $container) {
            $container->get('object');
        };

        $result = $container->runIsolated($context);

        $this->assertFalse($result->hasLeaks());
    }

    protected function createContainer(array $definitions = []): WeakRefIsolatedContainer
    {
        return new WeakRefIsolatedContainer($definitions);
    }
}
