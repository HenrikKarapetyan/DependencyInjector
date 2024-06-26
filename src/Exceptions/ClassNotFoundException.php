<?php

declare(strict_types=1);

namespace Henrik\DI\Exceptions;

use Throwable;

class ClassNotFoundException extends InjectorException
{
    public function __construct(string $idOrClass, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Class or id "%s" not found.', $idOrClass), $code, $previous);
    }
}