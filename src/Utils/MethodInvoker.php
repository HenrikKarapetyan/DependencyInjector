<?php

declare(strict_types=1);

namespace Henrik\DI\Utils;

use henrik\container\exceptions\IdAlreadyExistsException;
use henrik\container\exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\MethodNotFoundException;
use Henrik\DI\Exceptions\UnknownScopeException;
use ReflectionException;
use ReflectionMethod;

/**
 * Class MethodInvoker.
 */
class MethodInvoker
{
    use MethodORFunctionDependencyLoaderTrait;

    /**
     * @param object                   $obj
     * @param string                   $method
     * @param array<int|string, mixed> $args
     *
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws IdAlreadyExistsException
     * @throws \Henrik\DI\Exceptions\ServiceNotFoundException
     * @throws UnknownScopeException|ClassNotFoundException
     * @throws MethodNotFoundException
     *
     * @return mixed|null
     */
    public function invoke(object $obj, string $method, array $args = []): mixed
    {
        if (method_exists($obj, $method)) {
            $klass     = get_class($obj);
            $refMethod = new ReflectionMethod($klass, $method);
            $params    = $this->loadDependencies($refMethod->getParameters(), $args);

            return $refMethod->invokeArgs($obj, $params);
        }

        throw new MethodNotFoundException(sprintf('Method "%s" not found', $method));
    }
}