<?php

namespace Henrik\DI\Traits;

use Henrik\DI\Utils\AttributesParser;
use Henrik\DI\Utils\ClassCollector;

trait DIServicesFromClassesPathTrait
{
    /**
     * @param string             $path
     * @param string             $namespace
     * @param array<string>|null $excludedPaths
     *
     * @return void
     */
    public function loadFromPath(
        string $path,
        string $namespace,
        ?array $excludedPaths = []
    ): void {
        $loadedClasses = ClassCollector::collect(path: $path, namespace: $namespace, excludedPaths: $excludedPaths);
        AttributesParser::parse($this, $loadedClasses);

    }
}