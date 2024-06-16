<?php

namespace Henrik\DI\Test\Utils;

use Henrik\Contracts\Enums\InjectorModes;
use Henrik\Contracts\Enums\ServiceScope;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Exceptions\MethodNotFoundException;
use Henrik\DI\Test\SimpleServices\OtherUnregisteredClass;
use Henrik\DI\Utils\MethodInvoker;
use PHPUnit\Framework\TestCase;

class MethodInvokerTest extends TestCase
{
    private DependencyInjector $dependencyInjector;

    private MethodInvoker $methodInvoker;

    protected function setUp(): void
    {
        parent::setUp();

        $services = [
            ServiceScope::PARAM->value => [
                'name' => 'developer',
            ],
        ];
        $this->dependencyInjector = DependencyInjector::instance();
        $this->dependencyInjector->setMode(InjectorModes::AUTO_REGISTER);
        $this->dependencyInjector->load($services);
        $methodInvoker       = new MethodInvoker($this->dependencyInjector);
        $this->methodInvoker = $methodInvoker;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dependencyInjector->removeAllServices();
        unset($this->dependencyInjector);
    }

    public function testMethodInvoker(): void
    {
        /** @var OtherUnregisteredClass $objInstance */
        $objInstance = $this->dependencyInjector->get(OtherUnregisteredClass::class);

        $res = $this->methodInvoker->invoke($objInstance, 'simpleMethod', ['lastName' => 'developer']);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('name', $res);
        $this->assertArrayHasKey('lastName', $res);
        $this->assertEquals('developer', $res['name']);
        $this->assertEquals('developer', $res['lastName']);
    }

    public function testMethodInvokerForUnknownMethod(): void
    {
        /** @var OtherUnregisteredClass $objInstance */
        $objInstance = $this->dependencyInjector->get(OtherUnregisteredClass::class);
        $this->expectException(MethodNotFoundException::class);
        $this->methodInvoker->invoke($objInstance, 'simpleUnknownMethod', ['lastName' => 'developer']);
    }
}
