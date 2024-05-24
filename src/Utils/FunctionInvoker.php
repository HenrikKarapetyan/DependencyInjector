<?php

declare(strict_types=1);

namespace Henrik\DI\Utils;

use Closure;
use Henrik\Contracts\FunctionInvokerInterface;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Traits\MethodORFunctionDependencyLoaderTrait;
use ReflectionException;
use ReflectionFunction;

/**
 * Class FunctionInvoker.
 */
class FunctionInvoker implements FunctionInvokerInterface
{
    use MethodORFunctionDependencyLoaderTrait;

    /**
     * @param Closure                  $func
     * @param array<int|string, mixed> $args
     *
     * @throws ReflectionException
     * @throws ClassNotFoundException
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