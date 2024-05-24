<?php

declare(strict_types=1);

namespace Henrik\DI\Traits;

use Henrik\Contracts\DependencyInjectorInterface;
use Henrik\DI\Exceptions\ClassNotFoundException;
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
     * @throws ClassNotFoundException
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