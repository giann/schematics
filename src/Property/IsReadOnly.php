<?php

declare(strict_types=1);

namespace Giann\Schematics\Property;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class IsReadOnly extends Property
{
    public function __construct(bool $value = true)
    {
        parent::__construct("readOnly", $value);
    }
}
