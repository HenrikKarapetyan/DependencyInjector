<?php

namespace Henrik\DI\Test\SimpleServices;

class UnregisteredClass
{
    private int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): UnregisteredClass
    {
        $this->id = $id;

        return $this;
    }
}