<?php

namespace Henrik\DI\Test;

use Henrik\DI\Attributes\AsService;
use Henrik\DI\DependencyInjector;
use Henrik\DI\DIAttributesParser;
use Henrik\DI\Exceptions\AbstractClassAsDefinitionException;
use Henrik\DI\Test\SimpleServices\AnomalyClasses\AbstractClassByAttribute;
use Henrik\DI\Test\SimpleServices\ClassesWithServiceAttributes\ClassWithAsFactoryAttribute;
use Henrik\DI\Test\SimpleServices\ClassesWithServiceAttributes\ClassWithAsPrototypeAttribute;
use Henrik\DI\Test\SimpleServices\ClassesWithServiceAttributes\ClassWithAsSingletonAttribute;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class DIAttributesParserTest extends TestCase
{
    private DependencyInjector $dependencyInjector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dependencyInjector = DependencyInjector::instance();
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
    public static function classesWithAttributes(): array
    {

        return [
            'AsSingleton' => ['class' => ClassWithAsSingletonAttribute::class],
            'AsFactory'   => ['class' => ClassWithAsFactoryAttribute::class],
            'AsPrototype' => ['class' => ClassWithAsPrototypeAttribute::class],
        ];
    }

    /**
     * @param class-string $class
     *
     * @throws ReflectionException
     *
     * @return void
     */
    #[DataProvider('classesWithAttributes')]
    public function testAttributesParser(string $class): void
    {

        $refClass   = new ReflectionClass($class);
        $attributes = $refClass->getAttributes();

        foreach ($attributes as $attribute) {
            $attrInstance = $attribute->newInstance();
            if ($attrInstance instanceof AsService) {
                $attrParser = new DIAttributesParser($this->dependencyInjector);
                $attrParser->parse($attrInstance, $refClass);
            }
        }
        $instance = $this->dependencyInjector->get($class);
        $this->assertInstanceOf($class, $instance);
    }
}
