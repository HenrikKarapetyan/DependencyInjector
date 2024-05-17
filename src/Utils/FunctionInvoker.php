<?php

declare(strict_types=1);

namespace Henrik\DI\Utils;

use Closure;
use henrik\container\exceptions\IdAlreadyExistsException;
use henrik\container\exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\UnknownScopeException;
use ReflectionException;
use ReflectionFunction;

/**
 * Class FunctionInvoker.
 */
class FunctionInvoker
{
    use MethodORFunctionDependencyLoaderTrait;

    /**
     * @param Closure                  $func
     * @param array<int|string, mixed> $args
     *
     * @throws ClassNotFoundException
     * @throws IdAlreadyExistsException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws UnknownScopeException
     * @throws \Henrik\DI\Exceptions\ServiceNotFoundException
     *
     * @return mixed
     */
    public function invoke(Closure $func, array $args = []): mixed
    {
        $refFunc = new ReflectionFunction($func);
        $params  = $this->loadDependencies($refFunc->getParameters(), $args);

        return $refFunc->invokeArgs($params);
    }
}