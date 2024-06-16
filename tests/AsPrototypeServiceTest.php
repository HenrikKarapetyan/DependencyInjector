<?php

namespace Henrik\DI\Test;

use Faker\Factory;
use Faker\Generator;
use Henrik\Contracts\Enums\ServiceScope;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Test\SimpleServices\SimpleClasses\SimpleUserService;
use PHPUnit\Framework\TestCase;

class AsPrototypeServiceTest extends TestCase
{
    private DependencyInjector $dependencyInjector;

    private Generator $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = Factory::create();

        $services = [
            ServiceScope::PROTOTYPE->value => [
                [
                    'id'    => SimpleUserService::class,
                    'class' => SimpleUserService::class,
                ],
            ],
        ];

        $this->dependencyInjector = DependencyInjector::instance();
        $this->dependencyInjector->load($services); // @phpstan-ignore-line
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->dependencyInjector->removeAllServices();
        unset($this->dependencyInjector, $this->factory);
    }

    public function testAsPrototype(): void
    {
        $name     = $this->factory->name();
        $email    = $this->factory->email(); // @phpstan-ignore-line
        $lastName = $this->factory->lastName(); // @phpstan-ignore-line
        $password = $this->factory->password();

        /** @var SimpleUserService $userFromContainer */
        $userFromContainer = $this->dependencyInjector->get(SimpleUserService::class);
        $userFromContainer->setName($name)->setLastName($lastName)->setPassword($password)->setEmail($email);

        /** @var SimpleUserService $userFromContainer2 */
        $userFromContainer2 = $this->dependencyInjector->get(SimpleUserService::class);

        $this->assertNotEquals($userFromContainer, $userFromContainer2);
    }
}