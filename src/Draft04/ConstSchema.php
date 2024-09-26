<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class ConstSchema extends Schema
{
    public function __construct(Schema $constSchema)
    {
        parent::__construct(allOf: [$constSchema]);
    }
}
