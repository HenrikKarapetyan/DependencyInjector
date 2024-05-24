<?php

namespace Henrik\DI\Utils;

use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\DI\Attributes\AsService;
use Henrik\DI\Definition;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Exceptions\UnknownScopeException;
use ReflectionClass;
use RuntimeException;

class AttributesParser
{
    /**
     * @param DependencyInjector $dependencyInjector
     * @param array<string>      $loadedClasses
     *
     * @throws KeyAlreadyExistsException
     * @throws UnknownScopeException
     */
    public static function parse(DependencyInjector $dependencyInjector, array $loadedClasses): void
    {
        foreach ($loadedClasses as $class) {

            if (class_exists($class)) {
                $reflectionClass = new ReflectionClass($class);

                $handlerClass = $reflectionClass->getName();

                if ($reflectionClass->isAbstract()) {
                    throw new RuntimeException(
                        sprintf('The class `%s` cannot be abstract', $handlerClass)
                    );
                }

                $reflectionAttributes = $reflectionClass->getAttributes();

                foreach ($reflectionAttributes as $reflectionAttribute) {
                    if ($reflectionAttribute->newInstance() instanceof AsService) {
                        /** @var AsService $attributeInstance */
                        $attributeInstance = $reflectionAttribute->newInstance();

                        $definition = (new Definition())->setArgs($attributeInstance->args)
                            ->setParams($attributeInstance->params)
                            ->setClass($reflectionClass->getName())
                            ->setId($attributeInstance->id ?? $reflectionClass->getName());

                        $dependencyInjector->add($attributeInstance->scope->value, $definition);

                    }
                }

            }
        }

    }
}