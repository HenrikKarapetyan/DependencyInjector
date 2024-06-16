<?php

declare(strict_types=1);

namespace Henrik\DI\Exceptions;

use Throwable;

class AbstractClassAsDefinitionException extends InjectorException
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}