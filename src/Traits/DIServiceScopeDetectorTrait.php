<?php

declare(strict_types=1);

namespace Henrik\DI\Traits;

use Henrik\Contracts\Enums\ServiceScope;
use Henrik\DI\ServiceScopeInterfaces\FactoryAwareInterface;
use Henrik\DI\ServiceScopeInterfaces\PrototypeAwareInterface;
use Henrik\DI\ServiceScopeInterfaces\SingletonAwareInterface;

trait DIServiceScopeDetectorTrait
{
    private function guessServiceScope(string $class): ServiceScope
    {
        $classImplementedInterfaces = class_implements($class);

        if (is_array($classImplementedInterfaces) && count($classImplementedInterfaces) > 0) {

            if (isset($classImplementedInterfaces[SingletonAwareInterface::class])) {
                return ServiceScope::SINGLETON;
            }

            if (isset($classImplementedInterfaces[PrototypeAwareInterface::class])) {
                return ServiceScope::PROTOTYPE;
            }

            if (isset($classImplementedInterfaces[FactoryAwareInterface::class])) {
                return ServiceScope::FACTORY;
            }

        }

        return $this->getAutoLoadedClassesDefaultScope();
    }
}