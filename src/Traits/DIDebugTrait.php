<?php

declare(strict_types=1);

namespace Henrik\DI\Traits;

use Exception;
use Henrik\DI\Providers\ObjectProvider;

trait DIDebugTrait
{
    public function dumpContainer(): void
    {
        /** @var array<string, string> $containerData */
        $containerData = [];
        foreach ($this->serviceContainer->getAll() as $id => $containerItem) {

            $collectedInfo = [];
            if (is_object($containerItem)) {
                $collectedInfo['definitionServedInfo'] = sprintf("Id: %s, providerType: %s \n", $id, $containerItem::class);

                try {
                    if ($containerItem instanceof ObjectProvider) {
                        if (is_object($containerItem->provide())) {
                            $collectedInfo['implementedClass'] = $containerItem->provide()::class;
                        }
                    }
                } catch (Exception $e) {
                    var_dump($e->getMessage(), $id);
                }

            }

            $containerData[] = $collectedInfo;
        }
        var_dump(json_encode($containerData));
    }
}