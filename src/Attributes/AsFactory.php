<?php

namespace Henrik\DI\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsFactory
{
    public string $id;
}