<?php

namespace Henrik\DI\Test\SimpleServices;

class OtherUnregisteredClass
{
    private int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): OtherUnregisteredClass
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $name
     * @param string $lastName
     *
     * @return string[]
     */
    public function simpleMethod(string $name, string $lastName): array
    {

        return [
            'name'     => $name,
            'lastName' => $lastName,
        ];
    }
}