<?php
/**
 * Created by PhpStorm.
 * User: Henrik
 * Date: 4/3/2018
 * Time: 9:13 PM.
 */
declare(strict_types=1);

namespace Henrik\DI\Providers;

use Exception;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Exceptions\InvalidAliasException;

class AliasProvider extends ServiceProvider
{
    /**
     * @throws Exception
     *
     * @return mixed
     */
    public function provide(): mixed
    {
        if (is_string($this->definition->getValue())) {
            return DependencyInjector::instance()->get($this->definition->getValue());
        }

        throw new InvalidAliasException('Invalid alias provided');
    }
}