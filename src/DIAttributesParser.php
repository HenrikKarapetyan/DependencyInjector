<?php

namespace Henrik\DI;

use Henrik\Contracts\AttributeParser\AttributeParserInterface;
use Henrik\Contracts\DependencyInjectorInterface;
use Henrik\DI\Attributes\AsService;
use ReflectionClass;

readonly class DIAttributesParser implements AttributeParserInterface
{
    public function __construct(private DependencyInjectorInterface $dependencyInjector) {}

    public function parse(?object $attributeClass, ReflectionClass $reflectionClass): void
    {
        /** @var ?AsService $diAttribute */
        $diAttribute = $attributeClass;

        if ($diAttribute) {

            $definition = (new Definition())->setArgs($diAttribute->args)
                ->setParams($diAttribute->params)
                ->setClass($reflectionClass->getName())
                ->setId($diAttribute->id ?? $reflectionClass->getName());

            $this->dependencyInjector->add($diAttribute->scope->value, $definition);
        }
    }
}