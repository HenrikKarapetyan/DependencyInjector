<?php

declare(strict_types=1);

namespace Henrik\DI\Traits;

use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\Container\Exceptions\KeyNotFoundException;
use Henrik\Contracts\DefinitionInterface;
use Henrik\Contracts\Enums\InjectorModes;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\ServiceConfigurationException;
use Henrik\DI\Exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\UnknownScopeException;
use Henrik\DI\ReflectionClassesContainer;
use Henrik\DI\ServicesContainer;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

trait DIInstantiatorTrait
{
    /**
     * @var ServicesContainer
     */
    private ServicesContainer $serviceContainer;
    /**
     * @var ReflectionClassesContainer
     */
    private ReflectionClassesContainer $reflectionsContainer;

    /**
     * Injector constructor.
     */
    private function __construct()
    {
        $this->serviceContainer     = new ServicesContainer();
        $this->reflectionsContainer = new ReflectionClassesContainer();
    }

    /**
     * @param DefinitionInterface $definition
     *
     * @throws ClassNotFoundException
     * @throws ReflectionException
     * @throws ServiceConfigurationException
     * @throws ServiceNotFoundException
     * @throws UnknownScopeException
     * @throws KeyAlreadyExistsException
     * @throws KeyNotFoundException
     *
     * @return object
     */
    public function instantiate(DefinitionInterface $definition): object
    {
        /**
         * @var ReflectionClass<object> $reflectionClass
         */
        $reflectionClass = $this->reflectionsContainer->getReflectionClass((string) $definition->getClass());

        if (!$reflectionClass->isInstantiable()) {
            throw new ServiceConfigurationException(sprintf('The service %s constructor is private', $definition->getClass()));
        }

        $constructor = $reflectionClass->getConstructor();
        if (empty($constructor)) {
            $klass = $definition->getClass();
            $obj   = new $klass();

            return $this->initializeParams($obj, $definition->getParams());
        }

        $obj = $this->loadMethodDependencies($reflectionClass, $constructor, $definition->getArgs());

        return $this->initializeParams($obj, $definition->getParams());
    }

    /**
     * @param object               $obj
     * @param array<string, mixed> $params
     *
     * @throws KeyNotFoundException
     * @throws ServiceConfigurationException
     *
     * @return object
     */
    public function initializeParams(object $obj, array $params): object
    {
        /** @var string|array<string, array<string, string>|string> $attrValue */
        foreach ($params as $attrName => $attrValue) {
            $method = 'set' . ucfirst($attrName);

            if (!method_exists($obj, $method)) {
                throw new ServiceConfigurationException(
                    sprintf('Property %s not found in object %s', $attrName, json_encode($obj))
                );
            }

            if (!is_array($attrValue) && str_starts_with($attrValue, '#')) {
                $serviceId = trim($attrValue, '#');
                $attrValue = $this->serviceContainer->get($serviceId);
            }
            $obj->{$method}($attrValue);
        }

        return $obj;
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     * @param ReflectionMethod        $method
     * @param array<string, mixed>    $args
     *
     * @throws KeyNotFoundException
     * @throws ReflectionException
     * @throws ServiceNotFoundException
     * @throws UnknownScopeException|KeyAlreadyExistsException
     * @throws ClassNotFoundException
     *
     * @return object
     */
    private function loadMethodDependencies(ReflectionClass $reflectionClass, ReflectionMethod $method, array $args): object
    {
        $constructorArguments = $method->getParameters();
        $reArgs               = [];
        if (count($constructorArguments) > 0) {

            foreach ($constructorArguments as $arg) {

                if ($arg->isDefaultValueAvailable()) {
                    if (!isset($args[$arg->getName()])) {
                        $reArgs[$arg->getName()] = $arg->getDefaultValue();

                        continue;
                    }
                    $reArgs[$arg->getName()] = $args[$arg->getName()];

                    continue;
                }

                if (isset($args[$arg->getName()])) {
                    if (is_string($args[$arg->getName()]) && str_starts_with($args[$arg->getName()], '#')) {
                        $serviceId               = trim($args[$arg->getName()], '#');
                        $reArgs[$arg->getName()] = $this->get($serviceId);

                        continue;
                    }
                    $reArgs[$arg->getName()] = $args[$arg->getName()];

                    continue;
                }

                $paramValue = $this->getValueFromContainer($arg);

                $reArgs[$arg->getName()] = $paramValue;

            }
        }

        return $reflectionClass->newInstanceArgs($reArgs);
    }

    /**
     * @param ReflectionParameter $arg
     *
     * @throws KeyNotFoundException
     * @throws ServiceNotFoundException
     * @throws UnknownScopeException|KeyAlreadyExistsException
     * @throws ClassNotFoundException
     *
     * @return mixed
     */
    private function getValueFromContainer(ReflectionParameter $arg): mixed
    {
        if ($this->serviceContainer->has($arg->getName())) {
            return $this->serviceContainer->get($arg->getName());
        }

        if (!$arg->getType() instanceof ReflectionNamedType) {
            throw new ClassNotFoundException($arg->getName());
        }

        if ($this->mode !== InjectorModes::AUTO_REGISTER) {

            if ($this->serviceContainer->has($arg->getType()->getName())) {
                $typeName = $arg->getType()->getName();

                return $this->serviceContainer->get($typeName);
            }

            throw new ServiceNotFoundException(sprintf('Service from "%s" not found in service container', $arg->getType()->getName()));

        }
        $typeName = $arg->getType()->getName();

        return $this->get($typeName);
    }
}