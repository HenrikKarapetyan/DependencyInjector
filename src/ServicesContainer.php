<?php

declare(strict_types=1);

namespace Henrik\DI;

use Henrik\Container\Container;
use Henrik\Container\ContainerModes;
use Henrik\Container\Exceptions\KeyNotFoundException;
use Henrik\Container\Exceptions\UndefinedModeException;
use Henrik\DI\Providers\ProviderInterface;

class ServicesContainer extends Container
{
    /**
     * ServicesContainer constructor.
     *
     * @throws UndefinedModeException
     */
    public function __construct()
    {
        $this->changeMode(ContainerModes::SINGLE_VALUE_MODE);
    }

    /**
     * @param string $id
     *
     * @throws KeyNotFoundException
     *
     * @return mixed
     */
    public function get(string $id): mixed
    {
        if ($this->has($id)) {
            $containerServedData = parent::get($id);
            if ($containerServedData instanceof ProviderInterface) {
                return $containerServedData->provide();
            }

            return $containerServedData;
        }

        return null;
    }
}