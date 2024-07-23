<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

enum Type: string
{
    case String = 'string';
    case Number = 'number';
    case Object = 'object';
    case Array = 'array';
    case Boolean = 'boolean';
    case Null = 'null';
}
