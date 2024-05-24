<?php
/**
 * Created by PhpStorm.
 * User: Henrik
 * Date: 4/3/2018
 * Time: 3:05 PM.
 */
declare(strict_types=1);

namespace Henrik\DI\Parsers;

use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\Container\Exceptions\UndefinedModeException;
use Henrik\Contracts\Enums\ServiceScope;
use Henrik\DI\Exceptions\InvalidConfigurationException;
use Henrik\DI\Exceptions\UnknownScopeException;
use Henrik\DI\Parsers\Scenarios\ClassParserScenario;
use Henrik\DI\Parsers\Scenarios\KeyValueParserScenario;

/**
 * Class ArrayConfigParser.
 */
class ArrayConfigParser extends AbstractConfigParser
{
    /**
     * @param array<string, array<string, int|string>> $services
     *
     * @throws UndefinedModeException
     */
    public function __construct(
        private readonly array $services
    ) {
        parent::__construct();
    }

    /**
     * @throws InvalidConfigurationException
     * @throws KeyAlreadyExistsException
     * @throws UnknownScopeException
     */
    public function parse(): void
    {
        foreach ($this->services as $scope => $serviceItems) {

            $this->parseEachScopeData($scope, $serviceItems);
        }

    }

    /**
     * @param string                    $scope
     * @param array<string, int|string> $serviceItems
     *
     * @throws InvalidConfigurationException|KeyAlreadyExistsException|UnknownScopeException
     *
     * @return void
     */
    private function parseEachScopeData(string $scope, array $serviceItems): void
    {
        foreach ($serviceItems as $item => $value) {

            $definition = match ($scope) {
                ServiceScope::FACTORY->value, ServiceScope::PROTOTYPE->value, ServiceScope::SINGLETON->value => ClassParserScenario::parse($value),
                ServiceScope::ALIAS->value, ServiceScope::PARAM->value => KeyValueParserScenario::parse($item, $value),
                default => throw new UnknownScopeException(sprintf("Unknown scope '%s'", $scope)),
            };
            $this->set($scope, $definition);
        }
    }
}