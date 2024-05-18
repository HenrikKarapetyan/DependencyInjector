<?php

declare(strict_types=1);

namespace Henrik\DI;

use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\Container\Exceptions\KeyNotFoundException;
use Henrik\Container\Exceptions\UndefinedModeException;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\ServiceConfigurationException;
use Henrik\DI\Exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\UnknownConfigurationException;
use Henrik\DI\Exceptions\UnknownScopeException;
use Henrik\DI\Providers\AliasProvider;
use Henrik\DI\Providers\FactoryProvider;
use Henrik\DI\Providers\ParamProvider;
use Henrik\DI\Providers\PrototypeProvider;
use Henrik\DI\Providers\SingletonProvider;
use Henrik\DI\ServiceScopeInterfaces\FactoryAwareInterface;
use Henrik\DI\ServiceScopeInterfaces\PrototypeAwareInterface;
use Henrik\DI\ServiceScopeInterfaces\SingletonAwareInterface;
use Hk\Contracts\DependencyInjectorInterface;
use Hk\Contracts\Enums\InjectorModes;
use Hk\Contracts\Enums\ServiceScope;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Class Injector.
 */
class DependencyInjector implements DependencyInjectorInterface
{
    use ConfigurationLoaderTrait;

    /**
     * @var ?self
     */
    private static ?DependencyInjector $instance = null;
    /**
     * @var ServicesContainer
     */
    private ServicesContainer $serviceContainer;
    /**
     * @var RCContainer
     */
    private RCContainer $reflectionsContainer;

    private InjectorModes $mode = InjectorModes::CONFIG_FILE;

    /**
     * Injector constructor.
     */
    private function __construct()
    {
        $this->serviceContainer     = new ServicesContainer();
        $this->reflectionsContainer = new RCContainer();
    }

    /**
     * @throws ClassNotFoundException
     * @throws KeyAlreadyExistsException
     * @throws KeyNotFoundException
     * @throws ServiceNotFoundException
     * @throws UnknownScopeException
     *
     * @return self
     */
    public static function instance(): DependencyInjector
    {
        if (self::$instance == null) {
            self::$instance = new self();
            if (!self::$instance->get(DependencyInjectorInterface::class, false)) {
                self::$instance->serviceContainer->set(DependencyInjectorInterface::class, self::$instance);
            }
        }

        return self::$instance;
    }

    /**
     * @param array<string, array<string, int|string>>|string $services
     *
     * @throws KeyAlreadyExistsException
     * @throws UndefinedModeException
     * @throws UnknownConfigurationException
     * @throws UnknownScopeException
     */
    public function load(array|string $services): void
    {
        $data = $this->guessExtensionOrDataType($services);

        foreach ($data as $scope => $definitionArray) {
            foreach ($definitionArray as $definition) {
                $this->add($scope, $definition);
            }
        }
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
     * @param string $id
     * @param bool   $throwError
     *
     * @throws KeyNotFoundException
     * @throws ServiceNotFoundException
     * @throws UnknownScopeException
     * @throws ClassNotFoundException
     * @throws KeyAlreadyExistsException
     *
     * @return mixed
     */
    public function get(string $id, bool $throwError = true): mixed
    {
        $dataFromContainer = $this->serviceContainer->get($id);
        if ($this->mode == InjectorModes::AUTO_REGISTER) {
            if ($dataFromContainer === null) {

                if (!class_exists($id)) {
                    throw new ClassNotFoundException($id);
                }

                $scope      = $this->guessServiceScope($id);
                $definition = new Definition($id, $id);
                $this->add($scope->value, $definition);

                return $this->serviceContainer->get($id);
            }

        }

        if (is_null($dataFromContainer) && $throwError) {
            throw new ServiceNotFoundException($id);
        }

        return $dataFromContainer;

    }

    public function getMode(): InjectorModes
    {
        return $this->mode;
    }

    public function setMode(InjectorModes $mode): void
    {
        $this->mode = $mode;
    }

    public function dumpContainer(): void
    {
        foreach ($this->serviceContainer->getAll() as $id => $containerItem) {
            if (is_object($containerItem)) {
                printf("Id: %s, class: %s \n", $id, get_class($containerItem));
            }
        }
    }

    public function has(string $getName): bool
    {
        return $this->serviceContainer->has($getName);
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
     * @param string              $scope
     * @param DefinitionInterface $definition
     *
     * @throws KeyAlreadyExistsException
     * @throws UnknownScopeException
     */
    private function add(string $scope, DefinitionInterface $definition): void
    {

        $providerInst = match ($scope) {
            ServiceScope::SINGLETON->value => new SingletonProvider($this, $definition),
            ServiceScope::FACTORY->value   => new FactoryProvider($this, $definition),
            ServiceScope::PROTOTYPE->value => new PrototypeProvider($this, $definition),
            ServiceScope::ALIAS->value     => new AliasProvider($this, $definition),
            ServiceScope::PARAM->value     => new ParamProvider($this, $definition),
            default                        => throw new UnknownScopeException(sprintf('Unknown  scope "%s"', $scope)),

        };
        $this->serviceContainer->set((string) $definition->getId(), $providerInst);
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

    private function guessServiceScope(string $id): ServiceScope
    {
        $classImplementedInterfaces = class_implements($id);

        if (is_array($classImplementedInterfaces) && count($classImplementedInterfaces) > 0) {

            if (in_array(SingletonAwareInterface::class, $classImplementedInterfaces)) {
                return ServiceScope::SINGLETON;
            }

            if (in_array(PrototypeAwareInterface::class, $classImplementedInterfaces)) {
                return ServiceScope::PROTOTYPE;
            }

            if (in_array(FactoryAwareInterface::class, $classImplementedInterfaces)) {
                return ServiceScope::FACTORY;
            }
        }

        return ServiceScope::SINGLETON;
    }
}