<?php

namespace Henrik\DI\Test\SimpleServices;

class ParametrizdedClass
{
    /** @var string[] */
    private array $cookies;

    public function __construct() {}

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
     * @return $this
     */
    public function setCookies(array $cookies): self
    {
        $this->cookies = $cookies;

        return $this;
    }
}