<?php

declare(strict_types=1);

namespace Henrik\DI\Providers;

use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\Container\Exceptions\KeyNotFoundException;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\ServiceConfigurationException;
use Henrik\DI\Exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\UnknownScopeException;
use ReflectionException;

/**
 * Class FactoryProvider.
 */
class FactoryProvider extends ObjectProvider
{
    /**
     * @throws KeyAlreadyExistsException
     * @throws KeyNotFoundException
     * @throws ClassNotFoundException
     * @throws UnknownScopeException
     * @throws ReflectionException|ServiceNotFoundException
     * @throws ServiceConfigurationException
     *
     * @return object
     */
    public function provide(): object
    {
        return $this->injector->instantiate($this->definition);
    }
}