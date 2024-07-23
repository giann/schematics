<?php

declare(strict_types=1);

namespace Giann\Schematics\December2020\Property;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class DefaultValue extends Property
{
    public function __construct(mixed $default)
    {
        parent::__construct('default', $default);
    }
}
