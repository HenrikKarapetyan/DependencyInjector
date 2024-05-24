<?php

namespace Henrik\DI\Utils;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ClassCollector
{
    /**
     * @param string             $path
     * @param string             $namespace
     * @param array<string>|null $excludedPaths
     *
     * @return array<string>
     */
    public static function collect(
        string $path,
        string $namespace,
        ?array $excludedPaths = []
    ): array {
        $classes  = [];
        $iterator = new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS);

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator($iterator) as $file) {

            $className = $file->getBasename('.' . $file->getExtension());

            if (is_array($excludedPaths) && in_array(needle: $iterator->getPathname(), haystack: $excludedPaths)) {
                continue;
            }

            $filePath = str_replace('/', '\\', str_replace($path, '', $file->getPath()));
            $class    = sprintf('%s%s\\%s', $namespace, $filePath, $className);

            if (class_exists($class)) {
                $classes[] = $class;
            }

        }

        return $classes;
    }
}