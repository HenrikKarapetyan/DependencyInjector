<?php

namespace Henrik\DI\Test\Utils;

use Henrik\Contracts\Enums\InjectorModes;
use Henrik\Contracts\Enums\ServiceScope;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Exceptions\UnknownTypeForParameterException;
use Henrik\DI\Test\SimpleServices\SimpleClasses\SimpleDefinition;
use Henrik\DI\Utils\FunctionInvoker;
use PHPUnit\Framework\TestCase;

class FunctionInvokerTest extends TestCase
{
    private DependencyInjector $dependencyInjector;

    private FunctionInvoker $functionInvoker;

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
        $functionInvoker       = new FunctionInvoker($this->dependencyInjector);
        $this->functionInvoker = $functionInvoker;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dependencyInjector->removeAllServices();
        unset($this->dependencyInjector);
    }

    public function testFunctionInvoker(): void
    {

        $res = $this->functionInvoker->invoke(
            function (string $name, string $lastName, SimpleDefinition $simpleDefinition, string $paramByDefaultValue = 'defaultValue') {

                return [
                    'name'                => $name,
                    'lastName'            => $lastName,
                    'simpleDefinition'    => $simpleDefinition,
                    'paramByDefaultValue' => $paramByDefaultValue,
                ];
            },
            ['lastName' => 'developer']
        );
        $this->assertIsArray($res);
        $this->assertArrayHasKey('name', $res);
        $this->assertArrayHasKey('lastName', $res);
        $this->assertEquals('developer', $res['name']);
        $this->assertEquals('developer', $res['lastName']);
        $this->assertEquals('defaultValue', $res['paramByDefaultValue']);
        $this->assertInstanceOf(SimpleDefinition::class, $res['simpleDefinition']);
    }

    public function testInvokeFunctionWithUnknownTypeParam(): void
    {
        $this->expectException(UnknownTypeForParameterException::class);

        $this->functionInvoker->invoke(
            function ($name) {

                return [
                    'name' => $name,
                ];
            },
        );
    }
}
