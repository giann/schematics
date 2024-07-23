<?php

declare(strict_types=1);

namespace Giann\Schematics\December2020\Property;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class Ref extends Property
{
    public function __construct(string $ref)
    {
        parent::__construct('$ref', $ref);
    }
}
