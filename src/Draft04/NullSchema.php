<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class NullSchema extends Schema
{
    /**
     * @param string|null $schema Will be ignored if not root of the schema
     * @param string|null $id
     * @param array<string,Schema|CircularReference|null> $definitions
     * @param string|null $title
     * @param string|null $description

     * @param boolean|null $readOnly
     * @param boolean|null $writeOnly
     */
    public function __construct(
        ?string $schema = null,
        ?string $title = null,
        ?string $id = null,
        array $definitions = [],
        ?string $description = null,

        ?bool $readOnly = null,
        ?bool $writeOnly = null,
    ) {
        parent::__construct(
            [Type::Null],
            schema: $schema,
            id: $id,
            definitions: $definitions,
            title: $title,
            description: $description,

            readOnly: $readOnly,
            writeOnly: $writeOnly,
        );
    }
}
