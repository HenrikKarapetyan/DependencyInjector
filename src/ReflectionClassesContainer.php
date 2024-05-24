<?php

declare(strict_types=1);

namespace Henrik\DI;

use Henrik\Container\Container;
use Henrik\Container\ContainerModes;
use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\Container\Exceptions\KeyNotFoundException;
use Henrik\Container\Exceptions\UndefinedModeException;
use ReflectionClass;

class ReflectionClassesContainer extends Container
{
    /**
     * ReflectionsContainer constructor.
     *
     * @throws UndefinedModeException
     */
    public function __construct()
    {
        $this->changeMode(ContainerModes::SINGLE_VALUE_MODE);
    }

    /**
     * @param string $klass
     *
     * @throws KeyAlreadyExistsException|KeyNotFoundException
     *
     * @return mixed
     */
    public function getReflectionClass(string $klass): mixed
    {
        if (!$this->has($klass) && class_exists($klass)) {
            $this->set($klass, new ReflectionClass($klass));
        }

        return $this->get($klass);
    }
}