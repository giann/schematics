<?php

declare(strict_types=1);

namespace Giann\Schematics\Property;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Examples extends Property
{
    /**
     * @param mixed[] $examples
     */
    public function __construct(array $examples)
    {
        parent::__construct('examples', $examples);
    }
}
