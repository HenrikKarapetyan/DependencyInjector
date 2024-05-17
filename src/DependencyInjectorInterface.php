<?php

namespace Henrik\DI;

interface DependencyInjectorInterface
{
    /**
     * @param array<string, array<string, int|string>>|string $services
     *
     * @return void
     */
    public function load(array|string $services): void;

    public function get(string $id, bool $throwError = true): mixed;

    public function dumpContainer(): void;
}