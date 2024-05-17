<?php

namespace Henrik\DI\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsPrototype
{
    public string $id;
}