<?php
/**
 * Created by PhpStorm.
 * User: Henrik
 * Date: 4/3/2018
 * Time: 9:02 PM.
 */
declare(strict_types=1);

namespace Henrik\DI\Providers;

use Exception;
use Henrik\DI\Exceptions\ServiceConfigurationException;

/**
 * Class SingletonProvider.
 */
class SingletonProvider extends ObjectProvider
{
    /**
     * @throws ServiceConfigurationException
     * @throws Exception
     *
     * @return object
     */
    public function provide(): object
    {
        if ($this->instance === null) {
            $this->instance = $this->injector->instantiate($this->definition);
        }

        return $this->instance;
    }
}