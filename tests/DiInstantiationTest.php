<?php

namespace Henrik\DI\Test;

use Faker\Factory;
use Henrik\Contracts\Enums\ServiceScope;
use Henrik\Contracts\Utils\MarkersInterface;
use Henrik\DI\Definition;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Test\SimpleServices\SimpleClasses\ClassByMultipleDependencies;
use Henrik\DI\Test\SimpleServices\SimpleClasses\SimpleDefinition;
use Henrik\DI\Test\SimpleServices\SimpleClasses\SimpleUserService;
use PHPUnit\Framework\TestCase;

class DiInstantiationTest extends TestCase
{
    private DependencyInjector $dependencyInjector;
    /**
     * @var array<string, string[]>
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
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dependencyInjector->removeAllServices();
        unset($this->dependencyInjector);
    }

    public function testDiInstantiation(): void
    {
        $age = 24;
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
        $this->assertInstanceOf($this->services[ServiceScope::SINGLETON->value][0]['class'], $instance->getSimpleUserService());
        $this->assertInstanceOf(SimpleDefinition::class, $instance->getSimpleDefinition());
    }
}