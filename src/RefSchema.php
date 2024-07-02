<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RefSchema extends Schema
{
    public function __construct(
        string $ref,
    ) {
        parent::__construct(ref: $ref);
    }
}
