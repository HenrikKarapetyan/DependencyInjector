<?php

namespace Henrik\DI\Test;

use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\Container\Exceptions\KeyNotFoundException;
use Henrik\Contracts\Enums\InjectorModes;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\UnknownScopeException;
use Henrik\DI\Test\SimpleServices\ServicesByInterfaces\AsFactoryServiceByInterface;
use Henrik\DI\Test\SimpleServices\ServicesByInterfaces\AsPrototypeServiceByInterface;
use Henrik\DI\Test\SimpleServices\ServicesByInterfaces\AsSingletonServiceByInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ServicesByInterfaceTest extends TestCase
{
    private DependencyInjector $dependencyInjector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dependencyInjector = DependencyInjector::instance();
        $this->dependencyInjector->setMode(InjectorModes::AUTO_REGISTER);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dependencyInjector->removeAllServices();
        unset($this->dependencyInjector);
    }

    /**
     * @return array<string, array<class-string>>
     */
    public static function classesWithInterfaces(): array
    {

        return [
            'AsSingleton' => ['class' => AsSingletonServiceByInterface::class],
            'AsFactory'   => ['class' => AsFactoryServiceByInterface::class],
            'AsPrototype' => ['class' => AsPrototypeServiceByInterface::class],
        ];
    }

    /**
     * @param string $class
     * @return void
     * @throws KeyAlreadyExistsException
     * @throws KeyNotFoundException
     * @throws ClassNotFoundException
     * @throws ServiceNotFoundException
     * @throws UnknownScopeException
     */
    #[DataProvider('classesWithInterfaces')]
    public function testServicesByInterfaces(string $class): void {

        $classInstance = $this->dependencyInjector->get($class);

        $this->assertInstanceOf($class, $classInstance);
    }
}