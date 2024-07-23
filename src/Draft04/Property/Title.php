<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04\Property;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class Title extends Property
{
    public function __construct(string $title)
    {
        parent::__construct('title', $title);
    }
}
