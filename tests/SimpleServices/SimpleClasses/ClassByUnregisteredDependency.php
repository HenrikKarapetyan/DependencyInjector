<?php

namespace Henrik\DI\Test\SimpleServices\SimpleClasses;

class ClassByUnregisteredDependency
{
    public function __construct(
        private SimpleDefinition $simpleDefinition,
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