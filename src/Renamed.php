<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Use to provide a different name for an object property in the resulting json schema
 */
class Renamed
{
    public function __construct(
        public string $name
    ) {
    }
}
