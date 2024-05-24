<?php

declare(strict_types=1);

namespace Henrik\DI;

use Henrik\Container\Container;
use Henrik\Container\ContainerModes;
use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\Container\Exceptions\KeyNotFoundException;
use Henrik\Container\Exceptions\UndefinedModeException;
use Henrik\Contracts\DefinitionInterface;
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
     * @param string            $id
     * @param ProviderInterface $provider
     *
     * @throws KeyAlreadyExistsException
     */
    public function add(string $id, ProviderInterface $provider): void
    {
        $this->set($id, $provider);
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

    /**
     * @param array<DefinitionInterface> $definitions
     *
     * @return void
     */
    public function bulkAdd(array $definitions): void
    {
        $this->data = array_merge_recursive($definitions);
    }
}