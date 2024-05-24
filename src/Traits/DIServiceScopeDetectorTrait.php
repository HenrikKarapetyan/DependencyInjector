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

        return $this->getAutoLoadedClassesDefaultScope();
    }
}