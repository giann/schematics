<?php

declare(strict_types=1);

namespace Giann\Schematics\Property;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class Type extends Property
{
    /**
     * @param Type[] $type
     */
    public function __construct(array $type)
    {
        parent::__construct("type", $type);
    }
}
