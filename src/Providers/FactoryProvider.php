<?php

declare(strict_types=1);

namespace Henrik\DI\Providers;

use henrik\container\exceptions\IdAlreadyExistsException;
use henrik\container\exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\ServiceConfigurationException;

/**
 * Class FactoryProvider.
 */
class FactoryProvider extends ObjectProvider
{
    /**
     * @throws \Henrik\DI\Exceptions\ServiceNotFoundException
     * @throws ServiceNotFoundException|IdAlreadyExistsException
     * @throws ServiceConfigurationException
     *
     * @return object
     */
    public function provide(): object
    {
        return $this->injector->instantiate($this->definition);
    }
}