<?php

namespace Henrik\DI\Parsers;

use Henrik\Container\Container;
use Henrik\Container\ContainerModes;
use Henrik\Container\Exceptions\UndefinedModeException;

abstract class AbstractConfigParser extends Container implements ConfigParserInterface
{
    /**
     * @throws UndefinedModeException
     */
    public function __construct()
    {
        $this->changeMode(ContainerModes::MULTIPLE_VALUE_MODE);
    }
}