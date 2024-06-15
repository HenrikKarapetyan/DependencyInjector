<?php

namespace Henrik\DI\Test;

use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\Container\Exceptions\KeyNotFoundException;
use Henrik\Container\Exceptions\UndefinedModeException;
use Henrik\Contracts\Enums\ServiceScope;
use Henrik\Contracts\Utils\MarkersInterface;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\UnknownConfigurationException;
use Henrik\DI\Exceptions\UnknownScopeException;
use Henrik\DI\Test\SimpleClasses\SimpleDefinition;
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
     * @throws ClassNotFoundException
     * @throws KeyAlreadyExistsException
     * @throws KeyNotFoundException
     * @throws ServiceNotFoundException
     * @throws UndefinedModeException
     * @throws UnknownConfigurationException
     * @throws UnknownScopeException
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

    protected function tearDown(): void
    {
        parent::tearDown();
       $this->dependencyInjector->removeAllServices();
    }
}