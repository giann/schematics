<?php

declare(strict_types=1);

namespace Giann\Schematics\Property;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class Ref extends Property
{
    public function __construct(string $ref)
    {
        parent::__construct("$ref", $ref);
    }
}
