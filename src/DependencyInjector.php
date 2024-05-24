<?php

declare(strict_types=1);

namespace Henrik\DI;

use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\Container\Exceptions\KeyNotFoundException;
use Henrik\Contracts\DefinitionInterface;
use Henrik\Contracts\DependencyInjectorInterface;
use Henrik\Contracts\Enums\InjectorModes;
use Henrik\Contracts\Enums\ServiceRegisterTypes;
use Henrik\Contracts\Enums\ServiceScope;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\ServiceNotFoundException;
use Henrik\DI\Exceptions\UnknownScopeException;
use Henrik\DI\Providers\AliasProvider;
use Henrik\DI\Providers\FactoryProvider;
use Henrik\DI\Providers\ParamProvider;
use Henrik\DI\Providers\PrototypeProvider;
use Henrik\DI\Providers\SingletonProvider;
use Henrik\DI\Traits\DIBehaviorTrait;
use Henrik\DI\Traits\DIDebugTrait;
use Henrik\DI\Traits\DIInstantiatorTrait;
use Henrik\DI\Traits\DIServiceScopeDetectorTrait;
use Henrik\DI\Traits\DIServicesFromClassesPathTrait;
use Henrik\DI\Traits\DIServicesFromFileTrait;

/**
 * Class Injector.
 */
class DependencyInjector implements DependencyInjectorInterface
{
    use DIServicesFromFileTrait;
    use DIInstantiatorTrait;
    use DIDebugTrait;
    use DIServiceScopeDetectorTrait;
    use DIBehaviorTrait;
    use DIServicesFromClassesPathTrait;
    /**
     * @var ?self
     */
    private static ?DependencyInjector $instance = null;

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

    /**
     * @param string              $scope
     * @param DefinitionInterface $definition
     *
     * @throws KeyAlreadyExistsException
     * @throws UnknownScopeException
     */
    public function add(string $scope, DefinitionInterface $definition): void
    {

        $providerInst = match ($scope) {
            ServiceScope::SINGLETON->value => new SingletonProvider($this, $definition),
            ServiceScope::FACTORY->value   => new FactoryProvider($this, $definition),
            ServiceScope::PROTOTYPE->value => new PrototypeProvider($this, $definition),
            ServiceScope::ALIAS->value     => new AliasProvider($this, $definition),
            ServiceScope::PARAM->value     => new ParamProvider($this, $definition),
            default                        => throw new UnknownScopeException(sprintf('Unknown  scope "%s"', $scope)),

        };

        $definitionId = (string) $definition->getId();

        switch ($this->serviceRegisterTypes) {
            case ServiceRegisterTypes::IGNORE_IF_EXISTS:
                if (!$this->serviceContainer->has($definitionId)) {
                    $this->serviceContainer->set($definitionId, $providerInst);
                }

                break;
            case ServiceRegisterTypes::REPLACE_IF_EXISTS:
                if ($this->serviceContainer->has($definitionId)) {
                    $this->serviceContainer->delete($definitionId);
                }
                $this->serviceContainer->set($definitionId, $providerInst);

                break;
            default: $this->serviceContainer->set($definitionId, $providerInst);
        }
    }

    public function has(string $getName): bool
    {
        return $this->serviceContainer->has($getName);
    }
}