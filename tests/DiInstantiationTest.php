<?php

namespace Henrik\DI\Test;

use Faker\Factory;
use Henrik\Contracts\Enums\InjectorModes;
use Henrik\Contracts\Enums\ServiceScope;
use Henrik\Contracts\Utils\MarkersInterface;
use Henrik\DI\Definition;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Exceptions\ServiceConfigurationException;
use Henrik\DI\Exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\UnknownConfigurationException;
use Henrik\DI\Exceptions\UnknownTypeForParameterException;
use Henrik\DI\Test\SimpleServices\AnomalyClasses\ClassWithPrivateConstructor;
use Henrik\DI\Test\SimpleServices\SimpleClasses\ClassByMultipleDependencies;
use Henrik\DI\Test\SimpleServices\SimpleClasses\ClassByUnknownTypeParameter;
use Henrik\DI\Test\SimpleServices\SimpleClasses\ClassByUnregisteredDependency;
use Henrik\DI\Test\SimpleServices\SimpleClasses\SimpleDefinition;
use Henrik\DI\Test\SimpleServices\SimpleClasses\SimpleUserService;
use PHPUnit\Framework\TestCase;

class DiInstantiationTest extends TestCase
{
    private DependencyInjector $dependencyInjector;
    /**
     * @var array<string, string[]|string[][]>
     */
    private array $services = [];

    protected function setUp(): void
    {
        parent::setUp();
        $factory = Factory::create();

        $this->services = [
            ServiceScope::PARAM->value => [
                'name'     => $factory->name(),
                'lastName' => $factory->lastName(), // @phpstan-ignore-line
            ],
            ServiceScope::SINGLETON->value => [
                [
                    'id'    => 'simpleUserService',
                    'class' => SimpleUserService::class,
                ],
            ],
        ];
        $this->dependencyInjector = DependencyInjector::instance();
        $this->dependencyInjector->load($this->services); // @phpstan-ignore-line
        $this->dependencyInjector->setMode(InjectorModes::AUTO_REGISTER);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dependencyInjector->removeAllServices();
        unset($this->dependencyInjector);
    }

    public function testDiInstantiation(): void
    {
        $age        = 24;
        $definition = new Definition(
            id: ClassByMultipleDependencies::class,
            class: ClassByMultipleDependencies::class,
            args: [
                'name' => MarkersInterface::AS_SERVICE_PARAM_MARKER . 'name',
                'age'  => $age,
            ],
            params: [
                'lastName' => MarkersInterface::AS_SERVICE_PARAM_MARKER . 'lastName',
            ]
        );

        /** @var ClassByMultipleDependencies $instance */
        $instance = $this->dependencyInjector->instantiate($definition);

        $this->assertEquals($this->services[ServiceScope::PARAM->value]['name'], $instance->getName());
        $this->assertEquals($this->services[ServiceScope::PARAM->value]['lastName'], $instance->getLastName());
        $this->assertEquals($age, $instance->getAge());
        $this->assertInstanceOf(SimpleUserService::class, $instance->getSimpleUserService());
        $this->assertInstanceOf(SimpleDefinition::class, $instance->getSimpleDefinition());
    }

    public function testInstantiateClassWithPrivateConstructor(): void
    {
        $this->expectException(ServiceConfigurationException::class);
        $this->dependencyInjector->get(ClassWithPrivateConstructor::class);
    }

    public function testUnregisteredClassAsMethodParam(): void
    {
        $this->dependencyInjector->setMode(InjectorModes::CONFIG_FILE);
        $definition = new Definition(
            id: ClassByUnregisteredDependency::class,
            class: ClassByUnregisteredDependency::class
        );

        $this->expectException(ServiceNotFoundException::class);
        $this->dependencyInjector->instantiate($definition);
    }

    public function testGetRegisteredDependencyFromContainer(): void
    {
        $this->dependencyInjector->setMode(InjectorModes::CONFIG_FILE);
        $this->dependencyInjector->load([ // @phpstan-ignore-line
            ServiceScope::SINGLETON->value => [
                [
                    'id'    => SimpleDefinition::class,
                    'class' => SimpleDefinition::class,
                ],
            ],
        ]);

        $definition = new Definition(
            id: ClassByUnregisteredDependency::class,
            class: ClassByUnregisteredDependency::class
        );

        /** @var ClassByUnregisteredDependency $instance */
        $instance = $this->dependencyInjector->instantiate($definition);

        $this->assertInstanceOf(ClassByUnregisteredDependency::class, $instance);
    }

    public function testRaiseExceptionIfClassNotFound(): void
    {
        $this->dependencyInjector->setMode(InjectorModes::CONFIG_FILE);
        $definition = new Definition(
            id: ClassByUnknownTypeParameter::class,
            class: ClassByUnknownTypeParameter::class
        );

        $this->expectException(UnknownTypeForParameterException::class);
        $this->dependencyInjector->instantiate($definition);
    }

    public function testRaiseExceptionForUnknownConfigurationType(): void
    {
        $this->expectException(UnknownConfigurationException::class);
        $this->dependencyInjector->load('unknownType');
    }
}