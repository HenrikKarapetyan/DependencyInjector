<?php

namespace Henrik\DI\Test\SimpleServices\SimpleClasses;

class ClassByUnknownTypeParameter
{
    /**
     * @param mixed $simpleDefinition
     */
    public function __construct(
        private $simpleDefinition,
    ) {}

    public function getSimpleDefinition(): SimpleDefinition
    {
        return $this->simpleDefinition;
    }

    public function setSimpleDefinition(SimpleDefinition $simpleDefinition): self
    {
        $this->simpleDefinition = $simpleDefinition;

        return $this;
    }
}