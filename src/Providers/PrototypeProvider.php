<?php
/**
 * Created by PhpStorm.
 * User: Henrik
 * Date: 4/3/2018
 * Time: 9:03 PM.
 */
declare(strict_types=1);

namespace Henrik\DI\Providers;

use Exception;
use Henrik\DI\Exceptions\ServiceConfigurationException;
use Henrik\DI\Exceptions\ServiceNotFoundException;

class PrototypeProvider extends ObjectProvider
{
    /**
     * @throws ServiceConfigurationException
     * @throws ServiceNotFoundException
     * @throws Exception
     *
     * @return object
     */
    public function provide(): object
    {
        if ($this->instance === null) {
            $this->instance = $this->injector->instantiate($this->definition);
        }

        return clone $this->instance;
    }
}