<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04\Property;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class IsWriteOnly extends Property
{
    public function __construct(bool $value = true)
    {
        parent::__construct("writeOnly", $value);
    }
}
