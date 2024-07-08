<?php

declare(strict_types=1);

namespace Giann\Schematics\Property;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Id extends Property
{
    public function __construct(string $id)
    {
        parent::__construct('$id', $id);
    }
}
