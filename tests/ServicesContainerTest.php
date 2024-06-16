<?php

namespace Henrik\DI\Test;

use Henrik\DI\Definition;
use Henrik\DI\ServicesContainer;
use PHPUnit\Framework\TestCase;

class ServicesContainerTest extends TestCase
{
    public function testServiceContainer(): void
    {

        $serviceContainer = new ServicesContainer();

        $serviceContainer->set('simpleId', new Definition());

        $this->assertInstanceOf(Definition::class, $serviceContainer->get('simpleId'));
    }
}
