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
use henrik\container\exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\ServiceConfigurationException;

class PrototypeProvider extends ObjectProvider
{
    /**
     * @throws ServiceNotFoundException
     * @throws ServiceConfigurationException
     * @throws \Henrik\DI\Exceptions\ServiceNotFoundException
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