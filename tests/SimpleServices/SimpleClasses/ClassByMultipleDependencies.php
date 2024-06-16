<?php

namespace Henrik\DI\Test\SimpleServices\SimpleClasses;

class ClassByMultipleDependencies
{
    private string $lastName;

    /**
     * @param SimpleUserService $simpleUserService
     * @param SimpleDefinition $simpleDefinition
     * @param string $name
     * @param int $age
     * @param string[] $paramByDefaultValue
     */
    public function __construct(
        private SimpleUserService $simpleUserService,
        private SimpleDefinition $simpleDefinition,
        private string $name,
        private int $age,
        private array $paramByDefaultValue = [],
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getParamByDefaultValue(): array
    {
        return $this->paramByDefaultValue;
    }

    /**
     * @param string[] $paramByDefaultValue
     *
     * @return $this
     */
    public function setParamByDefaultValue(array $paramByDefaultValue): self
    {
        $this->paramByDefaultValue = $paramByDefaultValue;

        return $this;
    }

    public function getSimpleUserService(): SimpleUserService
    {
        return $this->simpleUserService;
    }

    public function setSimpleUserService(SimpleUserService $simpleUserService): self
    {
        $this->simpleUserService = $simpleUserService;
        return $this;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;
        return $this;
    }

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