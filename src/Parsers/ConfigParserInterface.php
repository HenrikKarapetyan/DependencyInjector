<?php

namespace Henrik\DI\Parsers;

use Henrik\Contracts\DefinitionInterface;

interface ConfigParserInterface
{
    /**
     * @return void
     */
    public function parse(): void;

    /**
     * @return array<string, array<DefinitionInterface>>
     */
    public function getAll(): array;
}