<?php

namespace Henrik\DI\Test\Utils;

use Henrik\Contracts\Enums\InjectorModes;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Exceptions\AbstractClassAsDefinitionException;
use Henrik\DI\Exceptions\ServiceNotFoundException;
use Henrik\DI\Test\SimpleServices\ClassesWithServiceAttributes\ClassWithAsSingletonAttribute;
use Henrik\DI\Test\SimpleServices\SimpleClasses\SimpleUserService;
use PHPUnit\Framework\TestCase;

class ClassCollectorTest extends TestCase
{
    private DependencyInjector $dependencyInjector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dependencyInjector = DependencyInjector::instance();
        $this->dependencyInjector->setMode(InjectorModes::CONFIG_FILE);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dependencyInjector->removeAllServices();
        unset($this->dependencyInjector);
    }

    public function testClassCollector(): void
    {
        $this->dependencyInjector->loadFromPath('tests/SimpleServices', '\\Henrik\\DI\\Test\\SimpleServices', ['tests/SimpleServices/SimpleClasses', 'tests/SimpleServices/ServicesByInterfaces', 'tests/SimpleServices/AnomalyClasses']);

        $inst = $this->dependencyInjector->get(ClassWithAsSingletonAttribute::class);
        $this->assertInstanceOf(ClassWithAsSingletonAttribute::class, $inst);

        $this->expectException(ServiceNotFoundException::class);
        $this->dependencyInjector->get(SimpleUserService::class);
    }


    public function testClassCollectorByAbstractClass(): void
    {
        $this->expectException(AbstractClassAsDefinitionException::class);
        $this->dependencyInjector->loadFromPath('tests/SimpleServices/AnomalyClasses', '\\Henrik\\DI\\Test\\SimpleServices\\AnomalyClasses');
    }

}
