<?php

declare(strict_types=1);

namespace Henrik\DI\Traits;

use Henrik\Container\Exceptions\KeyAlreadyExistsException;
use Henrik\Container\Exceptions\UndefinedModeException;
use Henrik\Contracts\DefinitionInterface;
use Henrik\DI\Exceptions\UnknownConfigurationException;
use Henrik\DI\Exceptions\UnknownScopeException;
use Henrik\DI\Parsers\ArrayConfigParser;
use Henrik\DI\Parsers\ConfigParserInterface;

trait DIServicesFromFileTrait
{
    /**
     * @param array<string, array<string, int|string>>|string $services
     *
     * @throws KeyAlreadyExistsException
     * @throws UndefinedModeException
     * @throws UnknownConfigurationException
     * @throws UnknownScopeException
     */
    public function load(array|string $services): void
    {
        $data = $this->guessExtensionOrDataType($services);

        foreach ($data as $scope => $definitionArray) {
            foreach ($definitionArray as $definition) {
                $this->add($scope, $definition);
            }
        }
    }

    /**
     * @param string|array<string, array<string, int|string>> $services
     *
     * @throws UnknownConfigurationException
     * @throws UndefinedModeException
     *
     * @return ConfigParserInterface
     */
    private function guessDataType(array|string $services): ConfigParserInterface
    {
        if (is_array($services)) {
            return new ArrayConfigParser($services);
        }

        throw new UnknownConfigurationException();
    }

    /**
     * @param string|array<string, array<string, int|string>> $services
     *
     * @throws UndefinedModeException
     * @throws UnknownConfigurationException
     *
     * @return array<string, array<DefinitionInterface>>
     */
    private function guessExtensionOrDataType(array|string $services): array
    {
        $configParser = $this->guessDataType($services);

        $configParser->parse();

        return $configParser->getAll();
    }
}