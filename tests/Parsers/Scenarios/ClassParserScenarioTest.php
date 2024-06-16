<?php

namespace Henrik\DI\Test\Parsers\Scenarios;

use Henrik\Contracts\DefinitionInterface;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\InvalidConfigurationException;
use Henrik\DI\Parsers\Scenarios\ClassParserScenario;
use Henrik\DI\Test\SimpleServices\SimpleClasses\SimpleUserService;
use PHPUnit\Framework\TestCase;

class ClassParserScenarioTest extends TestCase
{
    public function testDiServicesConfigurationByTags(): void
    {
        $definitionArray = [
            'id'    => SimpleUserService::class,
            'class' => SimpleUserService::class,
        ];
        $res = ClassParserScenario::parse($definitionArray);
        $this->assertInstanceOf(DefinitionInterface::class, $res);
    }

    public function testWithoutClassAttribute(): void
    {
        // pass without `class` attribute
        $definitionArray = [
            'id' => SimpleUserService::class,
        ];

        $this->expectException(InvalidConfigurationException::class);
        ClassParserScenario::parse($definitionArray);
    }

    public function testByUnknownClass(): void
    {
        // pass unknown `class`
        $definitionArray = [
            'id'    => SimpleUserService::class,
            'class' => 'xxxxx',
        ];
        $this->expectException(ClassNotFoundException::class);
        ClassParserScenario::parse($definitionArray);
    }

    public function testByInvalidIdAndClassAttributesValueTypes(): void
    {
        $definitionArray = [
            'id'    => 1,
            'class' => 45,
        ];
        $this->expectException(InvalidConfigurationException::class);
        ClassParserScenario::parse($definitionArray);
    }

    public function testParseWithParamsAttribute(): void
    {
        $definitionArray = [
            'id'     => SimpleUserService::class,
            'class'  => SimpleUserService::class,
            'params' => [
                'name'     => 'developer',
                'lastName' => 'developer',
            ],
        ];
        $definition = ClassParserScenario::parse($definitionArray);

        $this->assertInstanceOf(DefinitionInterface::class, $definition);
        $this->assertEquals('developer', $definition->getParams()['name']);
        $this->assertEquals('developer', $definition->getParams()['lastName']);
    }

    public function testParseWithWrongParamsAttributeValueType(): void
    {
        $definitionArray = [
            'id'     => SimpleUserService::class,
            'class'  => SimpleUserService::class,
            'params' => [
                0 => 'developer',
            ],
        ];
        $this->expectException(InvalidConfigurationException::class);
        ClassParserScenario::parse($definitionArray);
    }

    public function testParseWithoutIdAndClassAttributes(): void
    {
        $name            = 'developer';
        $lastname        = 'developer';
        $definitionArray = [
            SimpleUserService::class,
            [
                'params' => [
                    'name'     => $name,
                    'lastName' => $lastname,
                ],
                'args' => [],
            ],
        ];
        $res = ClassParserScenario::parse($definitionArray);
        $this->assertInstanceOf(DefinitionInterface::class, $res);
        $this->assertEquals('developer', $res->getParams()['name']);
        $this->assertEquals('developer', $res->getParams()['lastName']);
    }

    public function testParseWithoutIdAndClassAttributesByWrongValueType(): void
    {
        $definitionArray = [
            23,
        ];
        $this->expectException(InvalidConfigurationException::class);
        ClassParserScenario::parse($definitionArray);
    }
}
