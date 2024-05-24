<?php
/**
 * Created by PhpStorm.
 * User: Henrik
 * Date: 4/3/2018
 * Time: 8:48 PM.
 */
declare(strict_types=1);

namespace Henrik\DI\Providers;

use Henrik\Contracts\DefinitionInterface;
use Henrik\DI\DependencyInjector;

/**
 * Class ServiceProvider.
 */
abstract class ServiceProvider implements ProviderInterface
{
    /**
     * ServiceProvider constructor.
     *
     * @param DependencyInjector  $injector
     * @param DefinitionInterface $definition
     */
    public function __construct(
        protected DependencyInjector $injector,
        protected DefinitionInterface $definition
    ) {}
}