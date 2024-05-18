<?php

namespace Henrik\DI;

use Henrik\Container\Exceptions\UndefinedModeException;
use Henrik\DI\Exceptions\UnknownConfigurationException;
use Henrik\DI\Parsers\ArrayConfigParser;
use Henrik\DI\Parsers\ConfigParserInterface;

trait ConfigurationLoaderTrait
{
    /**
     * @param string|array<string, array<string, int|string>> $services
     *
     * @throws UnknownConfigurationException
     * @throws UndefinedModeException
     *
     * @return ConfigParserInterface
     */
    public function guessDataType(array|string $services): ConfigParserInterface
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