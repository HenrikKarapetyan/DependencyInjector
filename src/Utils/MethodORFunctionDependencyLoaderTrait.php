<?php

declare(strict_types=1);

namespace Henrik\DI\Utils;

use henrik\container\exceptions\IdAlreadyExistsException;
use henrik\container\exceptions\ServiceNotFoundException;
use Henrik\DI\DependencyInjector;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\UnknownScopeException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Trait MethodORFunctionDependencyLoaderTrait.
 */
trait MethodORFunctionDependencyLoaderTrait
{
    public function __construct(private readonly DependencyInjector $dependencyInjector) {}

    /**
     * @param array<int, reflectionParameter> $methodParams
     * @param array<int|string, mixed>        $args
     *
     * @throws \Henrik\DI\Exceptions\ServiceNotFoundException
     * @throws UnknownScopeException|ClassNotFoundException
     * @throws ServiceNotFoundException
     * @throws IdAlreadyExistsException
     *
     * @return array<int, mixed>
     */
    private function loadDependencies(array $methodParams, array $args = []): array
    {
        $params = [];

        if (!empty($methodParams)) {

            foreach ($methodParams as $param) {

                if ($param->isDefaultValueAvailable() && !isset($args[$param->getName()])) {
                    $params[] = $param->getDefaultValue();
                }

                if (isset($args[$param->getName()])) {
                    $params[] = $args[$param->getName()];

                    continue;
                }

                if (!$param->getType() instanceof ReflectionNamedType) {
                    throw new ClassNotFoundException($param->getName());
                }
                if ($this->dependencyInjector->has($param->getName())) {
                    $params[] = $this->dependencyInjector->get($param->getName());

                    continue;
                }
                $params[] = $this->dependencyInjector->get($param->getType()->getName());

            }
        }

        return $params;
    }
}