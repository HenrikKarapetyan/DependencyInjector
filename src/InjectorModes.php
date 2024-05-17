<?php

namespace Henrik\DI;

enum InjectorModes: string
{
    case AUTO_REGISTER = 'AUTO_REGISTER';

    case CONFIG_FILE = 'CONFIG_FILE';
}