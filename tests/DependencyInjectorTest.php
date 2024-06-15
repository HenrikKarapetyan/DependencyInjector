<?php

namespace Henrik\DI\Test;

use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\Container\Exceptions\KeyNotFoundException;
use Henrik\Container\Exceptions\UndefinedModeException;
use Henrik\Contracts\Enums\InjectorModes;
use Henrik\Contracts\Enums\ServiceRegisterTypes;
use Henrik\Contracts\Enums\ServiceScope;
use Henrik\Contracts\Utils\MarkersInterface;
use Henrik\DI\Definition;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\ServiceConfigurationException;
use Henrik\DI\Exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\ServiceParameterNotFoundException;
use Henrik\DI\Exceptions\UnknownConfigurationException;
use Henrik\DI\Exceptions\UnknownScopeException;
use Henrik\DI\Test\SimpleServices\AnomalyClasses\ClassWithPrivateConstructor;
use Henrik\DI\Test\SimpleServices\OtherUnregisteredClass;
use Henrik\DI\Test\SimpleServices\ParametrizdedClass;
use Henrik\DI\Test\SimpleServices\SimpleClasses\SimpleDefinition;
use Henrik\DI\Test\SimpleServices\UnregisteredClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DependencyInjectorTest extends TestCase
{
    private DependencyInjector $dependencyInjector;

    /** @var string[] */
    private array $cookies;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cookies = ['simpleCookie', 'simpleValue'];

        $services = [
            ServiceScope::PARAM->value => [
                'cookies_data' => $this->cookies,
            ],
        ];

        $this->dependencyInjector = DependencyInjector::instance();
        $this->dependencyInjector->load($services); // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dependencyInjector->removeAllServices();
    }

    /**
     * @return array<string, array<string, array<int|string, array<int|string, array<int|string, array<string, string>|string>>|string>>>
     */
    public static function dataProviderServices(): array
    {

        return [
            'asSingleton' => [
                'services' => [
                    ServiceScope::SINGLETON->value => [
                        [
                            'id'    => SimpleDefinition::class,
                            'class' => SimpleDefinition::class,
                            'args'  => [
                                'cookies' => MarkersInterface::AS_SERVICE_PARAM_MARKER . 'cookies_data',
                            ],
                        ],
                    ],
                ],
            ],

            'asPrototype' => [
                'services' => [
                    ServiceScope::PROTOTYPE->value => [
                        [
                            'id'    => SimpleDefinition::class,
                            'class' => SimpleDefinition::class,
                            'args'  => [
                                'cookies' => MarkersInterface::AS_SERVICE_PARAM_MARKER . 'cookies_data',
                            ],
                        ],
                    ],
                ],
            ],
            'asFactory' => [
                'services' => [
                    ServiceScope::FACTORY->value => [
                        [
                            'id'    => SimpleDefinition::class,
                            'class' => SimpleDefinition::class,
                            'args'  => [
                                'cookies' => MarkersInterface::AS_SERVICE_PARAM_MARKER . 'cookies_data',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<string, array<string, array<int|string, array<int|string, array<int|string, array<string, string>|string>>|string>>> $services
     *
     * @throws KeyAlreadyExistsException
     * @throws KeyNotFoundException
     * @throws ServiceNotFoundException
     * @throws UndefinedModeException
     * @throws UnknownConfigurationException
     * @throws UnknownScopeException
     * @throws ClassNotFoundException
     *
     * @return void
     */
    #[DataProvider(methodName: 'dataProviderServices')]
    public function testDependencyInjector(array $services): void
    {

        $this->dependencyInjector->load($services); // @phpstan-ignore-line

        /** @var SimpleDefinition $simpleDefinition */
        $simpleDefinition = $this->dependencyInjector->get(SimpleDefinition::class);

        $this->assertInstanceOf(SimpleDefinition::class, $simpleDefinition);

        $serviceCookies = $simpleDefinition->getCookies();
        $this->assertEquals($serviceCookies, $this->cookies);
    }

    public function testGetUnknownService(): void
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->dependencyInjector->get(UnregisteredClass::class);

        $this->dependencyInjector->setMode(InjectorModes::AUTO_REGISTER);
        $this->dependencyInjector->setAutoLoadedClassesDefaultScope(ServiceScope::SINGLETON);

        $serviceInstance = $this->dependencyInjector->get(UnregisteredClass::class);

        $this->assertInstanceOf(UnregisteredClass::class, $serviceInstance);

        $this->expectException(ClassNotFoundException::class);
        $this->dependencyInjector->get('simpleUnknownClass');
    }

    public function testAddServiceIntoDi(): void
    {
        $definition = new Definition();
        $definition->setId(UnregisteredClass::class);
        $definition->setClass(UnregisteredClass::class);

        $this->dependencyInjector->add(ServiceScope::SINGLETON->value, $definition);
        /** @var UnregisteredClass $serviceInstance */
        $serviceInstance = $this->dependencyInjector->get(UnregisteredClass::class);
        $serviceInstance->setId(34);
        $this->assertInstanceOf(UnregisteredClass::class, $serviceInstance);

        // in here container checking if service already exists then just ignoring new defined service by current id
        $this->dependencyInjector->setServiceRegisterTypes(ServiceRegisterTypes::IGNORE_IF_EXISTS);
        $this->dependencyInjector->add(ServiceScope::FACTORY->value, $definition);
        $this->dependencyInjector->setServiceRegisterTypes(ServiceRegisterTypes::IGNORE_IF_EXISTS);
        $this->dependencyInjector->add(ServiceScope::PROTOTYPE->value, new Definition(
            id: OtherUnregisteredClass::class,
            class: OtherUnregisteredClass::class
        ));

        $otherUnregisteredClassInstance = $this->dependencyInjector->get(OtherUnregisteredClass::class);
        $this->assertInstanceOf(OtherUnregisteredClass::class, $otherUnregisteredClassInstance);
        $newServiceInstance = $this->dependencyInjector->get(UnregisteredClass::class);
        $this->assertEquals($newServiceInstance, $serviceInstance);

        // in this we set this config for the service container then its automatically replacing current service by new one
        $this->dependencyInjector->setServiceRegisterTypes(ServiceRegisterTypes::REPLACE_IF_EXISTS);
        $this->dependencyInjector->add(ServiceScope::FACTORY->value, $definition);
        $newServiceInstance = $this->dependencyInjector->get(UnregisteredClass::class);

        $this->assertNotEquals($newServiceInstance, $serviceInstance);

        $isServiceAvailable = $this->dependencyInjector->has(UnregisteredClass::class);
        $this->assertTrue($isServiceAvailable);

    }

    public function testInstantiateClassWithPrivateConstructor(): void
    {
        $this->dependencyInjector->setMode(InjectorModes::AUTO_REGISTER);
        $this->expectException(ServiceConfigurationException::class);
        $this->dependencyInjector->get(ClassWithPrivateConstructor::class);
    }

    public function testDependencyInjectorParametrizedClasses(): void
    {

        // perfext data
        $cookies = ['simpleName', 'simpleValue'];
        $this->dependencyInjector->setMode(InjectorModes::AUTO_REGISTER);

        $definition = new Definition(
            id: ParametrizdedClass::class,
            class: ParametrizdedClass::class
        );
        $definition->setParams(['cookies' => $cookies]);
        $this->dependencyInjector->add(ServiceScope::SINGLETON->value, $definition);
        /** @var ParametrizdedClass $serviceInstance */
        $serviceInstance = $this->dependencyInjector->get(ParametrizdedClass::class);
        $this->assertEquals($serviceInstance->getCookies(), $cookies);

        $definition->setParams(['unknownParameter' => 'value']);
        $this->dependencyInjector->setServiceRegisterTypes(ServiceRegisterTypes::REPLACE_IF_EXISTS);
        $this->dependencyInjector->add(ServiceScope::SINGLETON->value, $definition);
        $this->expectException(ServiceParameterNotFoundException::class);
        $this->dependencyInjector->get(ParametrizdedClass::class);

        $this->dependencyInjector->load([ // @phpstan-ignore-line
            ServiceScope::PARAM->value => [
                'cookie' => $cookies,
            ],
        ]);

        // get params value from service params
        $definition->setParams(['cookies' => MarkersInterface::AS_SERVICE_PARAM_MARKER . 'cookie']);
        $this->dependencyInjector->setServiceRegisterTypes(ServiceRegisterTypes::REPLACE_IF_EXISTS);
        $this->dependencyInjector->add(ServiceScope::SINGLETON->value, $definition);
        /** @var ParametrizdedClass $serviceInstance */
        $serviceInstance = $this->dependencyInjector->get(ParametrizdedClass::class);
        $this->assertEquals($serviceInstance->getCookies(), $cookies);
    }
}