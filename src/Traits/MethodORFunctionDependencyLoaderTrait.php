<?php

declare(strict_types=1);

namespace Henrik\DI\Traits;

use Henrik\Contracts\DependencyInjectorInterface;
use Henrik\DI\Exceptions\UnknownTypeForParameterException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Trait MethodORFunctionDependencyLoaderTrait.
 */
trait MethodORFunctionDependencyLoaderTrait
{
    public function __construct(private readonly DependencyInjectorInterface $dependencyInjector) {}

    /**
     * @param array<int, reflectionParameter> $methodParams
     * @param array<int|string, mixed>        $args
     *
     * @throws UnknownTypeForParameterException
     *
     * @return array<int, mixed>
     */
    private function loadDependencies(array $methodParams, array $args = []): array
    {
        $params = [];

        if (!empty($methodParams)) {

            foreach ($methodParams as $param) {
                $params[] = $this->getMethodArgumentAndValues($param, $args);
            }
        }

        return $params;
    }

    /**
     * @param array<int|string, mixed> $args
     * @param ReflectionParameter      $param
     *
     * @throws UnknownTypeForParameterException
     *
     * @return mixed
     */
    private function getMethodArgumentAndValues(ReflectionParameter $param, array $args = []): mixed
    {

        if ($param->isDefaultValueAvailable() && !isset($args[$param->getName()])) {
            return $param->getDefaultValue();

        }

        if (isset($args[$param->getName()])) {
            return $args[$param->getName()];
        }

        if (!$param->getType() instanceof ReflectionNamedType) {
            throw new UnknownTypeForParameterException($param->getName());
        }

        if ($this->dependencyInjector->has($param->getName())) {
            return $this->dependencyInjector->get($param->getName());
        }

        return $this->dependencyInjector->get($param->getType()->getName());
    }
}