<?php

use Henrik\Contracts\Enums\ServiceScope;
use Henrik\Contracts\Utils\MarkersInterface;
use Henrik\DI\SampleClasses\SampleClass;

return [
    ServiceScope::PARAM->value => [
        'cookies' => ['sads', 'asdas'],
    ],

    ServiceScope::SINGLETON->value => [
        [
            'id'    => SampleClass::class,
            'class' => SampleClass::class,
            'args'  => [
                'cookiess' => MarkersInterface::AS_SERVICE_PARAM_MARKER . 'cookies',
            ],
        ],
    ],
];