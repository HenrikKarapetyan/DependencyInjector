<?php

namespace Henrik\DI\Test\SimpleServices\SimpleClasses;

class SimpleDefinition
{
    /**
     * @param string[] $cookies
     */
    public function __construct(private array $cookies = []) {}

    /**
     * @return string[]
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * @param string[] $cookies
     *
     * @return void
     */
    public function setCookies(array $cookies): void
    {
        $this->cookies = $cookies;
    }
}