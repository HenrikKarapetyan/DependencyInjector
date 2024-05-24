<?php

declare(strict_types=1);

namespace Henrik\DI\Traits;

use Henrik\Contracts\Enums\InjectorModes;
use Henrik\Contracts\Enums\ServiceRegisterTypes;
use Henrik\Contracts\Enums\ServiceScope;

trait DIBehaviorTrait
{
    private InjectorModes $mode = InjectorModes::CONFIG_FILE;

    private ServiceScope $autoLoadedClassesDefaultScope = ServiceScope::PROTOTYPE;

    private ServiceRegisterTypes $serviceRegisterTypes = ServiceRegisterTypes::THROW_ERROR_IF_EXISTS;

    public function getAutoLoadedClassesDefaultScope(): ServiceScope
    {
        return $this->autoLoadedClassesDefaultScope;
    }

    public function setAutoLoadedClassesDefaultScope(ServiceScope $autoLoadedClassesDefaultScope): void
    {
        $this->autoLoadedClassesDefaultScope = $autoLoadedClassesDefaultScope;
    }

    public function getServiceRegisterTypes(): ServiceRegisterTypes
    {
        return $this->serviceRegisterTypes;
    }

    public function setServiceRegisterTypes(ServiceRegisterTypes $serviceRegisterTypes): void
    {
        $this->serviceRegisterTypes = $serviceRegisterTypes;
    }

    public function getMode(): InjectorModes
    {
        return $this->mode;
    }

    public function setMode(InjectorModes $mode): void
    {
        $this->mode = $mode;
    }
}