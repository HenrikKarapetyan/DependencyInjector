<?php

namespace Henrik\DI\Traits;

use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\DI\Exceptions\AbstractClassAsDefinitionException;
use Henrik\DI\Exceptions\UnknownScopeException;
use Henrik\DI\Utils\AttributesParser;
use Henrik\Filesystem\Filesystem;

trait DIServicesFromClassesPathTrait
{
    /**
     * @param string             $path
     * @param string             $namespace
     * @param array<string>|null $excludedPaths
     *
     * @throws UnknownScopeException|AbstractClassAsDefinitionException
     * @throws KeyAlreadyExistsException
     *
     * @return void
     */
    public function loadFromPath(
        string $path,
        string $namespace,
        ?array $excludedPaths = []
    ): void {
        $loadedClasses = Filesystem::getPhpClassesFromDirectory(directory: $path, namespace: $namespace, excludedPaths: $excludedPaths);
        AttributesParser::parse($this, $loadedClasses);

    }
}