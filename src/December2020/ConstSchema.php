<?php

declare(strict_types=1);

namespace Giann\Schematics\December2020;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class ConstSchema extends Schema
{
    public function __construct(public readonly Schema $constSchema)
    {
        parent::__construct(allOf: [$constSchema]);
    }

    public function jsonSerialize(): array
    {
        return $this->constSchema->jsonSerialize();
    }
}
