<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class OneOfSchema extends Schema
{
    /**
     * @param Schema[] $schemas
     */
    public function __construct(
        array $schemas,
    ) {
        parent::__construct(oneOf: $schemas);
    }
}
