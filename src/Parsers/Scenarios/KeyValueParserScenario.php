<?php

namespace Henrik\DI\Parsers\Scenarios;

use Henrik\Contracts\DefinitionInterface;
use Henrik\DI\Definition;

class KeyValueParserScenario
{
    /**
     * @param string $item
     * @param mixed  $value
     *
     * @return DefinitionInterface
     */
    public static function parse(string $item, mixed $value): DefinitionInterface
    {
        $definition = new Definition();
        $definition->setId($item);
        $definition->setValue($value);

        return $definition;
    }
}