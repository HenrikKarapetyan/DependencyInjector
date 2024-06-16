<?php

namespace Henrik\DI\Parsers\Scenarios;

use Henrik\Contracts\DefinitionInterface;
use Henrik\DI\Definition;
use Henrik\DI\Exceptions\ClassNotFoundException;
use Henrik\DI\Exceptions\InvalidConfigurationException;

class ClassParserScenario
{
    /**
     * @param array<int, array<string, array<array<int|string, mixed>>|string|null>|string> $definitionArray
     *
     * @throws ClassNotFoundException
     * @throws InvalidConfigurationException
     *
     * @return DefinitionInterface
     */
    public static function parse(array $definitionArray): DefinitionInterface
    {
        $definition = self::parseAsAssocArray($definitionArray) ?? self::parseWithoutId($definitionArray); // @phpstan-ignore-line

        if (!$definition) {
            throw new InvalidConfigurationException('Invalid configuration!');
        }

        return $definition;
    }

    /**
     * [
     *      'id' =>'di',
     *      'class' => 'Henrik\DI\DI',
     *      'params' => []
     * ].
     *
     * @param array<string, string|array<array<int|string, mixed>>|string|null> $definitionArray
     *
     * @throws InvalidConfigurationException|ClassNotFoundException
     *
     * @return ?DefinitionInterface
     */
    private static function parseAsAssocArray(array $definitionArray): ?DefinitionInterface
    {
        if (isset($definitionArray['id'], $definitionArray['class'])) {

            if (is_string($definitionArray['id']) && is_string($definitionArray['class'])) {

                if (!class_exists($definitionArray['class'])) {
                    throw new ClassNotFoundException(
                        sprintf('Class `%s` does not exist', $definitionArray['class'])
                    );
                }

                $definition = new Definition();

                $definition->setId($definitionArray['id']);
                $definition->setClass($definitionArray['class']);

                if (isset($definitionArray['args']) && is_array($definitionArray['args'])) {
                    $definition->setArgs(self::parseParams($definitionArray['args']));
                }

                if (isset($definitionArray['params'])) {

                    $definition->setParams(self::parseParams($definitionArray['params']));
                }

                return $definition;
            }

            throw new InvalidConfigurationException(
                'Invalid configuration! The keys `id` and `class` are required and must be strings.'
            );
        }

        return null;
    }

    /**
     * @param array<array<int|string, mixed>>|string|null $params
     *
     * @throws InvalidConfigurationException
     *
     * @return array<string, mixed>
     */
    private static function parseParams(null|array|string $params): array
    {

        $parsedParams = [];
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                if (!is_string($key)) {
                    throw new InvalidConfigurationException('The `params` option must be assoc array and `key` must be string');
                }
                $parsedParams[$key] = $value;
            }
        }

        return $parsedParams;
    }

    /**
     * [
     *      Henrik\DI\Di, ['params' => ['dd'=>'dd'], 'args'=> ['dd'=>'dd']]
     * ].
     *
     * @param array<int, string|array<string, array<array<int|string, mixed>>|string|null>> $definitionArray
     *
     * @throws InvalidConfigurationException
     *
     * @return ?DefinitionInterface
     */
    private static function parseWithoutId(array $definitionArray): ?DefinitionInterface
    {
        if (isset($definitionArray[0])) {
            if (!is_string($definitionArray[0])) {
                throw new InvalidConfigurationException('The array first value must be string');
            }
            $definition = new Definition($definitionArray[0], $definitionArray[0]);
            $definition->setId($definitionArray[0]);
            $definition->setClass($definitionArray[0]);
            if (isset($definitionArray[1]) && is_array($definitionArray[1])) {
                if (isset($definitionArray[1]['args'])) {
                    $definition->setArgs(self::parseParams($definitionArray[1]['args']));
                }
                if (isset($definitionArray[1]['params'])) {
                    $definition->setParams(self::parseParams($definitionArray[1]['params']));
                }
            }

            return $definition;
        }

        return null;
    }
}