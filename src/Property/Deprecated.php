<?php

declare(strict_types=1);

namespace Giann\Schematics\Property;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class Deprecated extends Property
{
    public function __construct(bool $value = true)
    {
        parent::__construct("deprecated", $value);
    }
}
