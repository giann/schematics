<?php

declare(strict_types=1);

namespace Giann\Schematics\Property;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Anchor extends Property
{
    public function __construct(string $anchor)
    {
        parent::__construct("$anchor", $anchor);
    }
}
