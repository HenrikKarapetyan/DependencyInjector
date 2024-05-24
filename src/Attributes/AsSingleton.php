<?php

namespace Henrik\DI\Attributes;

use Attribute;
use Henrik\Contracts\Enums\ServiceScope;

#[Attribute(Attribute::TARGET_CLASS)]
class AsSingleton extends AsService
{
    /**
     * @param string|null          $id
     * @param array<string, mixed> $args
     * @param array<string, mixed> $params
     */
    public function __construct(?string $id = null, array $args = [], array $params = [])
    {
        parent::__construct($id, ServiceScope::SINGLETON, $args, $params);
    }
}