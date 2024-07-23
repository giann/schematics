<?php

declare(strict_types=1);

namespace Giann\Schematics\December2020\Property;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class Anchor extends Property
{
    public function __construct(string $anchor)
    {
        parent::__construct('$anchor', $anchor);
    }
}
