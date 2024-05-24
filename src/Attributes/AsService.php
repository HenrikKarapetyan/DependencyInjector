<?php

namespace Henrik\DI\Attributes;

use Attribute;
use Henrik\Contracts\Enums\ServiceScope;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class AsService
{
    /**
     * @param string|null          $id
     * @param ServiceScope         $scope
     * @param array<string, mixed> $args
     * @param array<string, mixed> $params
     */
    public function __construct(
        public ?string $id = null,
        public ServiceScope $scope = ServiceScope::SINGLETON,
        public array $args = [],
        public array $params = [],
    ) {}
}